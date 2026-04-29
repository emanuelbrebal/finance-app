<?php

namespace App\Domain\Importers\DTOs;

class ParsedTransaction
{
    public function __construct(
        public readonly string $occurredOn,   // Y-m-d
        public readonly string $description,
        public readonly string $amount,        // always positive decimal string
        public readonly string $direction,     // 'in' | 'out'
        public readonly ?string $externalId = null,
    ) {}

    public function dedupHash(int $accountId): string
    {
        return hash('sha256', implode('|', [
            $this->occurredOn,
            $this->amount,
            $this->direction,
            $this->description,
            $accountId,
        ]));
    }
}
