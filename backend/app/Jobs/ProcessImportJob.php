<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        private readonly int $batchId,
        private readonly string $storedPath,
        private readonly ?string $importerHint,
        private readonly ?array $mapping,
    ) {}

    public function handle(ImportService $service): void
    {
        $batch = ImportBatch::find($this->batchId);

        if ($batch === null) {
            return;
        }

        $service->process($batch, $this->storedPath, $this->importerHint, $this->mapping);
    }
}
