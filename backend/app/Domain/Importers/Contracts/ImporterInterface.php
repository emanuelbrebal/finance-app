<?php

namespace App\Domain\Importers\Contracts;

use App\Models\Account;
use Illuminate\Http\UploadedFile;

interface ImporterInterface
{
    public static function id(): string;

    public static function displayName(): string;

    public function canHandle(UploadedFile $file): bool;

    /** @return array<\App\Domain\Importers\DTOs\ParsedTransaction> */
    public function parse(UploadedFile $file, Account $account): array;
}
