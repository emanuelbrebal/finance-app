<?php

namespace App\Domain\Importers;

use App\Domain\Importers\Concerns\ReadsCsv;
use App\Domain\Importers\Contracts\ImporterInterface;
use App\Domain\Importers\DTOs\ParsedTransaction;
use App\Domain\Importers\Exceptions\InvalidImportFileException;
use App\Models\Account;
use Illuminate\Http\UploadedFile;

class GenericCsvImporter implements ImporterInterface
{
    use ReadsCsv;

    /**
     * Column mapping injected before parse().
     * Keys: 'date', 'description', 'amount', 'direction' (optional)
     * Values: actual column names in the CSV.
     *
     * @var array<string, string>
     */
    private array $mapping = [];

    public static function id(): string
    {
        return 'generic_csv';
    }

    public static function displayName(): string
    {
        return 'CSV Genérico (qualquer banco)';
    }

    public function withMapping(array $mapping): static
    {
        $this->mapping = $mapping;
        return $this;
    }

    public function canHandle(UploadedFile $file): bool
    {
        return strtolower($file->getClientOriginalExtension()) === 'csv';
    }

    public function parse(UploadedFile $file, Account $account): array
    {
        if (empty($this->mapping['date']) || empty($this->mapping['description']) || empty($this->mapping['amount'])) {
            throw new InvalidImportFileException('Column mapping must include: date, description, amount.');
        }

        $rows = $this->readCsvWithHeaders($file);

        if (empty($rows)) {
            throw new InvalidImportFileException('The CSV file has no data rows.');
        }

        $dateCol = $this->mapping['date'];
        $descCol = $this->mapping['description'];
        $amountCol = $this->mapping['amount'];
        $directionCol = $this->mapping['direction'] ?? null;

        $result = [];

        foreach ($rows as $row) {
            $description = trim($row[$descCol] ?? '');

            if ($description === '' || !isset($row[$amountCol])) {
                continue;
            }

            $rawAmount = $this->parseAmount($row[$amountCol]);
            $amount = abs($rawAmount);

            if ($directionCol && isset($row[$directionCol])) {
                $dirRaw = strtolower(trim($row[$directionCol]));
                $direction = in_array($dirRaw, ['in', 'entrada', 'credito', 'crédito', 'credit', '+']) ? 'in' : 'out';
            } else {
                $direction = $rawAmount < 0 ? 'out' : 'in';
            }

            $result[] = new ParsedTransaction(
                occurredOn: $this->parseDate($row[$dateCol] ?? ''),
                description: $description,
                amount: number_format($amount, 2, '.', ''),
                direction: $direction,
            );
        }

        return $result;
    }
}
