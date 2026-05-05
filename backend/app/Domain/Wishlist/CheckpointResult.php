<?php

namespace App\Domain\Wishlist;

class CheckpointResult
{
    public function __construct(
        public readonly ?bool $passed,        // null = pending
        public readonly string $reason,
        public readonly float $progressPct,   // 0-100
    ) {}

    public static function passed(string $reason, float $pct = 100): self
    {
        return new self(true, $reason, $pct);
    }

    public static function failed(string $reason, float $pct = 0): self
    {
        return new self(false, $reason, $pct);
    }

    public static function pending(string $reason, float $pct = 0): self
    {
        return new self(null, $reason, $pct);
    }
}
