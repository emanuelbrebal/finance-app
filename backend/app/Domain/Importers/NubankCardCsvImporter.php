<?php

namespace App\Domain\Importers;

use App\Domain\Importers\Concerns\ReadsCsv;
use App\Domain\Importers\Contracts\ImporterInterface;
use App\Domain\Importers\DTOs\ParsedTransaction;
use App\Domain\Importers\Exceptions\InvalidImportFileException;
use App\Models\Account;
use Illuminate\Http\UploadedFile;

class NubankCardCsvImporter implements ImporterInterface
{
    use ReadsCsv;

    // Nubank cartão de crédito CSV headers: date,title,amount
    private const REQUIRED_HEADERS = ['date', 'title', 'amount'];

    public static function id(): string
    {
        return 'nubank_card_csv';
    }

    public static function displayName(): string
    {
        return 'CSV Nubank — Cartão de Crédito';
    }

    public function canHandle(UploadedFile $file): bool
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext !== 'csv') {
            return false;
        }

        try {
            $rows = $this->readCsvWithHeaders($file);
            if (empty($rows)) {
                return false;
            }
            $headers = array_keys($rows[0]);
            foreach (self::REQUIRED_HEADERS as $required) {
                if (!in_array($required, $headers, true)) {
                    return false;
                }
            }
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function parse(UploadedFile $file, Account $account): array
    {
        $rows = $this->readCsvWithHeaders($file);

        if (empty($rows)) {
            throw new InvalidImportFileException('The CSV file has no data rows.');
        }

        $result = [];

        foreach ($rows as $row) {
            $description = trim($row['title'] ?? '');

            if ($description === '') {
                continue;
            }

            $amount = abs($this->parseAmount($row['amount'] ?? '0'));

            // Credit card transactions are always 'out' (charges)
            // Negative amounts on the statement = refunds = 'in'
            $rawAmount = $this->parseAmount($row['amount'] ?? '0');
            $direction = $rawAmount < 0 ? 'in' : 'out';

            $result[] = new ParsedTransaction(
                occurredOn: $this->parseDate($row['date']),
                description: $description,
                amount: number_format($amount, 2, '.', ''),
                direction: $direction,
            );
        }

        return $result;
    }
}
