<?php

namespace App\Domain\Wishlist\Checkpoints;

use App\Domain\Wishlist\Checkpoints\Contracts\CheckpointInterface;
use App\Domain\Wishlist\CheckpointResult;
use App\Models\User;
use App\Models\WishlistItem;

class StillWantedCheckpoint implements CheckpointInterface
{
    public function id(): string
    {
        return 'still_wanted';
    }

    public function label(): string
    {
        return 'Confirmação de desejo';
    }

    public function evaluate(WishlistItem $item, User $user): CheckpointResult
    {
        $daysIn = (int) $item->created_at->diffInDays(now());

        // Before quarantine ends, this is asked at the end — keep pending
        if ($daysIn < $item->quarantine_days) {
            return CheckpointResult::pending(
                'Será perguntado ao fim da quarentena.',
            );
        }

        // After quarantine, if user has not been prompted recently, keep pending
        if ($item->last_review_prompt_at === null) {
            return CheckpointResult::pending(
                'Aguardando você confirmar se ainda quer.',
            );
        }

        // If they extended quarantine, that already pushed last_review_prompt_at and reset state
        // We treat any positive prompt response (kept the item, didn't extend, didn't abandon)
        // as implicit confirmation when last_review_prompt_at is recent (≤ 7 days)
        $daysSincePrompt = (int) \Carbon\Carbon::parse($item->last_review_prompt_at)->diffInDays(now());

        if ($daysSincePrompt <= 7) {
            return CheckpointResult::passed(
                'Você confirmou o desejo recentemente.',
            );
        }

        return CheckpointResult::pending(
            'Confirmação expirou — vamos perguntar de novo.',
        );
    }
}
