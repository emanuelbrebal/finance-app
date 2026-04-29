<?php

namespace App\Domain\Importers;

use App\Domain\Importers\Concerns\ReadsCsv;
use App\Domain\Importers\Contracts\ImporterInterface;
use App\Domain\Importers\DTOs\ParsedTransaction;
use App\Domain\Importers\Exceptions\InvalidImportFileException;
use App\Models\Account;
use Illuminate\Http\UploadedFile;

class NubankCsvImporter implements ImporterInterface
{
    use ReadsCsv;

    // Nubank conta CSV headers: Data,Valor,Identificador,Descrição
    private const REQUIRED_HEADERS = ['Data', 'Valor', 'Identificador'];

    public static function id(): string
    {
        return 'nubank_csv';
    }

    public static function displayName(): string
    {
        return 'CSV Nubank — Conta Corrente';
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
            $valor = $this->parseAmount($row['Valor'] ?? '0');
            $description = trim($row['Descrição'] ?? $row['Descricao'] ?? '');

            if ($description === '') {
                continue;
            }

            $result[] = new ParsedTransaction(
                occurredOn: $this->parseDate($row['Data']),
                description: $description,
                amount: number_format(abs($valor), 2, '.', ''),
                direction: $valor < 0 ? 'out' : 'in',
                externalId: $row['Identificador'] ?? null,
            );
        }

        return $result;
    }
}
