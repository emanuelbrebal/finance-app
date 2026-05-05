<?php

namespace App\Domain\Wishlist\Checkpoints;

use App\Domain\Wishlist\Checkpoints\Contracts\CheckpointInterface;
use App\Domain\Wishlist\CheckpointResult;
use App\Models\User;
use App\Models\WishlistItem;

class QuarantineCheckpoint implements CheckpointInterface
{
    public function id(): string
    {
        return 'quarantine';
    }

    public function label(): string
    {
        return 'Quarentena cumprida';
    }

    public function evaluate(WishlistItem $item, User $user): CheckpointResult
    {
        $daysIn = (int) $item->created_at->diffInDays(now());
        $required = $item->quarantine_days;

        if ($daysIn >= $required) {
            return CheckpointResult::passed("Você esperou {$daysIn} dias.");
        }

        $missing = $required - $daysIn;
        $pct = round(($daysIn / $required) * 100, 1);

        return CheckpointResult::failed(
            "Faltam {$missing} dias de quarentena.",
            $pct,
        );
    }
}
