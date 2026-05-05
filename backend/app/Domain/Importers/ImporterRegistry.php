<?php

namespace App\Domain\Importers;

use App\Domain\Importers\Contracts\ImporterInterface;
use App\Domain\Importers\Exceptions\UnsupportedFileFormatException;
use Illuminate\Http\UploadedFile;

class ImporterRegistry
{
    /** @param array<ImporterInterface> $importers */
    public function __construct(private readonly array $importers) {}

    public function detect(UploadedFile $file, ?string $hint = null): ImporterInterface
    {
        if ($hint !== null) {
            $byId = $this->byId($hint);
            if ($byId !== null) {
                return $byId;
            }
        }

        foreach ($this->importers as $importer) {
            if ($importer->canHandle($file)) {
                return $importer;
            }
        }

        throw new UnsupportedFileFormatException();
    }

    public function byId(string $id): ?ImporterInterface
    {
        foreach ($this->importers as $importer) {
            if ($importer::id() === $id) {
                return $importer;
            }
        }

        return null;
    }

    /** @return array<array{id: string, name: string}> */
    public function list(): array
    {
        return array_map(fn (ImporterInterface $i) => [
            'id' => $i::id(),
            'name' => $i::displayName(),
        ], $this->importers);
    }
}
