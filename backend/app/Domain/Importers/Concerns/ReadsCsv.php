<?php

namespace App\Domain\Importers\Concerns;

use App\Domain\Importers\Exceptions\InvalidImportFileException;
use Illuminate\Http\UploadedFile;

trait ReadsCsv
{
    /** @return array<array<string, string>> */
    protected function readCsvWithHeaders(UploadedFile $file): array
    {
        $content = $this->readWithFallbackEncoding($file->getPathname());
        $lines = array_filter(
            array_map('trim', explode("\n", str_replace("\r\n", "\n", $content))),
            fn (string $line) => $line !== '',
        );

        if (count($lines) < 2) {
            throw new InvalidImportFileException('The CSV file is empty or has no data rows.');
        }

        $lines = array_values($lines);
        $headers = str_getcsv(array_shift($lines));
        $headers = array_map('trim', $headers);

        $rows = [];
        foreach ($lines as $line) {
            $values = str_getcsv($line);
            if (count($values) !== count($headers)) {
                continue; // skip malformed rows silently
            }
            $rows[] = array_combine($headers, array_map('trim', $values));
        }

        return $rows;
    }

    protected function readWithFallbackEncoding(string $path): string
    {
        $content = file_get_contents($path);

        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }

        return $content;
    }

    protected function parseAmount(string $raw): float
    {
        // Handle formats: "1.234,56", "1,234.56", "-23.50", "R$ 1.234,56"
        $raw = preg_replace('/[^\d,.\-]/', '', $raw);

        // Brazilian format: last separator is comma
        if (preg_match('/,\d{2}$/', $raw)) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }

        return (float) $raw;
    }

    protected function parseDate(string $raw): string
    {
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];

        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, trim($raw));
            if ($dt !== false) {
                return $dt->format('Y-m-d');
            }
        }

        // Try Carbon as last resort
        try {
            return \Carbon\Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            throw new InvalidImportFileException("Could not parse date: {$raw}");
        }
    }
}
