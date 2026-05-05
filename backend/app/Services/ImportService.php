<?php

namespace App\Services;

use App\Domain\Categorization\CategorizationRuleApplier;
use App\Domain\Importers\DTOs\ParsedTransaction;
use App\Domain\Importers\ImporterRegistry;
use App\Models\ImportBatch;
use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImportService
{
    public function __construct(
        private readonly ImporterRegistry $registry,
        private readonly CategorizationRuleApplier $applier,
    ) {}

    /**
     * Upload file, create batch, dispatch async processing job.
     */
    public function upload(UploadedFile $file, int $accountId, int $userId, ?string $importerHint = null, ?array $mapping = null): ImportBatch
    {
        $fileHash = hash_file('sha256', $file->getPathname());

        // Block re-upload of same file (only if a completed batch exists)
        $existing = ImportBatch::where('user_id', $userId)
            ->where('file_hash', $fileHash)
            ->where('status', ImportBatch::STATUS_COMPLETED)
            ->first();

        if ($existing) {
            abort(422, "Este arquivo já foi importado em {$existing->created_at->format('d/m/Y')}.");
        }

        $path = $file->store("imports/{$userId}", 'local');

        $batch = ImportBatch::create([
            'user_id' => $userId,
            'account_id' => $accountId,
            'importer' => $importerHint ?? 'auto',
            'original_filename' => $file->getClientOriginalName(),
            'file_hash' => $fileHash,
            'status' => ImportBatch::STATUS_PENDING,
            'rows_total' => 0,
            'rows_imported' => 0,
            'rows_duplicated' => 0,
        ]);

        \App\Jobs\ProcessImportJob::dispatch($batch->id, $path, $importerHint, $mapping);

        return $batch;
    }

    /**
     * Parse file synchronously and store preview payload on the batch.
     * Called by ProcessImportJob.
     */
    public function process(ImportBatch $batch, string $storedPath, ?string $importerHint, ?array $mapping): void
    {
        try {
            $fullPath = storage_path("app/{$storedPath}");
            $uploadedFile = new \Illuminate\Http\File($fullPath);
            $fakeUploaded = new UploadedFile($fullPath, $batch->original_filename, null, null, true);

            $account = $batch->account;
            $user = $batch->user;

            $importer = $this->registry->detect($fakeUploaded, $importerHint);

            if ($importer instanceof \App\Domain\Importers\GenericCsvImporter && $mapping) {
                $importer->withMapping($mapping);
            }

            $parsed = $importer->parse($fakeUploaded, $account);

            $existingHashes = Transaction::withTrashed()
                ->where('user_id', $user->id)
                ->pluck('dedup_hash')
                ->flip()
                ->all();

            $rows = [];
            $duplicatedCount = 0;

            foreach ($parsed as $i => $tx) {
                $hash = $tx->dedupHash($account->id);
                $isDuplicate = isset($existingHashes[$hash]);

                if ($isDuplicate) {
                    $duplicatedCount++;
                }

                $suggestedCategoryId = $isDuplicate ? null : $this->applier->suggest($tx->description, $user);

                $rows[] = [
                    'index' => $i,
                    'occurred_on' => $tx->occurredOn,
                    'description' => $tx->description,
                    'amount' => $tx->amount,
                    'direction' => $tx->direction,
                    'external_id' => $tx->externalId,
                    'dedup_hash' => $hash,
                    'is_duplicate' => $isDuplicate,
                    'suggested_category_id' => $suggestedCategoryId,
                    'category_id' => $suggestedCategoryId,
                ];
            }

            $batch->update([
                'importer' => $importer::id(),
                'status' => ImportBatch::STATUS_PREVIEW_READY,
                'rows_total' => count($parsed),
                'rows_duplicated' => $duplicatedCount,
                'preview_payload' => $rows,
            ]);
        } catch (\Throwable $e) {
            $batch->update([
                'status' => ImportBatch::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Persist confirmed rows as real transactions.
     *
     * @param array<array{row_index: int, category_id: ?int}> $overrides
     */
    public function confirm(ImportBatch $batch, array $overrides): array
    {
        if (!$batch->isPreviewReady()) {
            abort(422, 'Import batch is not ready for confirmation.');
        }

        $overrideMap = collect($overrides)->keyBy('row_index');
        $rows = $batch->preview_payload ?? [];
        $imported = 0;
        $skipped = 0;

        DB::transaction(function () use ($batch, $rows, $overrideMap, &$imported, &$skipped) {
            foreach ($rows as $row) {
                if ($row['is_duplicate']) {
                    $skipped++;
                    continue;
                }

                $categoryId = $overrideMap->has($row['index'])
                    ? $overrideMap->get($row['index'])['category_id']
                    : $row['category_id'];

                try {
                    Transaction::create([
                        'user_id' => $batch->user_id,
                        'account_id' => $batch->account_id,
                        'import_batch_id' => $batch->id,
                        'category_id' => $categoryId,
                        'occurred_on' => $row['occurred_on'],
                        'description' => $row['description'],
                        'amount' => $row['amount'],
                        'direction' => $row['direction'],
                        'dedup_hash' => $row['dedup_hash'],
                    ]);
                    $imported++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                    // Race condition: another process inserted it; skip silently
                    $skipped++;
                }
            }

            $batch->update([
                'status' => ImportBatch::STATUS_COMPLETED,
                'rows_imported' => $imported,
            ]);
        });

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Soft-delete all transactions from this batch and mark it reverted.
     */
    public function revert(ImportBatch $batch): void
    {
        if (!$batch->isCompleted()) {
            abort(422, 'Only completed batches can be reverted.');
        }

        DB::transaction(function () use ($batch) {
            $batch->transactions()->delete();
            $batch->update(['status' => ImportBatch::STATUS_REVERTED]);
        });
    }
}
