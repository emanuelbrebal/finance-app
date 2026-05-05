<?php

namespace App\Domain\Milestones\DTOs;

class MilestoneDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $tier,        // small | medium | large | epic
        public readonly string $title,
        public readonly string $body,
        public readonly string $dedupKey,
        public readonly array $payload = [],
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'tier' => $this->tier,
            'title' => $this->title,
            'body' => $this->body,
            'dedup_key' => $this->dedupKey,
            'payload' => $this->payload,
        ];
    }
}
