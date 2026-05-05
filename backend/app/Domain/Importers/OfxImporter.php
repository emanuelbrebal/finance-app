<?php

namespace App\Domain\Importers;

use App\Domain\Importers\Contracts\ImporterInterface;
use App\Domain\Importers\DTOs\ParsedTransaction;
use App\Domain\Importers\Exceptions\InvalidImportFileException;
use App\Models\Account;
use Illuminate\Http\UploadedFile;
use OfxParser\Parser;

class OfxImporter implements ImporterInterface
{
    public static function id(): string
    {
        return 'ofx';
    }

    public static function displayName(): string
    {
        return 'OFX (Nubank, Itaú, Bradesco, Santander e outros)';
    }

    public function canHandle(UploadedFile $file): bool
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, ['ofx', 'qfx'])) {
            return true;
        }

        // Sniff first bytes for OFX header
        $handle = fopen($file->getPathname(), 'r');
        $header = fread($handle, 512);
        fclose($handle);

        return str_contains($header, 'OFXHEADER') || str_contains($header, '<OFX>');
    }

    public function parse(UploadedFile $file, Account $account): array
    {
        try {
            $content = $this->readWithFallbackEncoding($file->getPathname());
            $tempPath = sys_get_temp_dir() . '/' . uniqid('ofx_') . '.ofx';
            file_put_contents($tempPath, $content);

            $ofx = (new Parser())->loadFromFile($tempPath);
            @unlink($tempPath);
        } catch (\Throwable $e) {
            throw new InvalidImportFileException('Could not parse OFX file: ' . $e->getMessage());
        }

        $bankAccount = $ofx->bankAccounts[0] ?? $ofx->creditAccount ?? null;

        if ($bankAccount === null) {
            throw new InvalidImportFileException('No bank account data found in the OFX file.');
        }

        $result = [];

        foreach ($bankAccount->statement->transactions as $t) {
            $rawAmount = (float) $t->amount;
            $result[] = new ParsedTransaction(
                occurredOn: $t->date->format('Y-m-d'),
                description: trim((string) ($t->memo ?: $t->name)),
                amount: number_format(abs($rawAmount), 2, '.', ''),
                direction: $rawAmount < 0 ? 'out' : 'in',
                externalId: $t->uniqueId ?: null,
            );
        }

        return $result;
    }

    private function readWithFallbackEncoding(string $path): string
    {
        $content = file_get_contents($path);

        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }

        return $content;
    }
}
