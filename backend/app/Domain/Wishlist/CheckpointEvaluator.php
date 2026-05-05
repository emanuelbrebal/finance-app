<?php

namespace App\Domain\Wishlist;

use App\Domain\Wishlist\Checkpoints\Contracts\CheckpointInterface;
use App\Models\User;
use App\Models\WishlistItem;

class CheckpointEvaluator
{
    /** @param array<CheckpointInterface> $checkpoints */
    public function __construct(private readonly array $checkpoints) {}

    /** @return array<array{id: string, label: string, passed: ?bool, reason: string, progress_pct: float}> */
    public function evaluate(WishlistItem $item, User $user): array
    {
        return array_map(function (CheckpointInterface $cp) use ($item, $user) {
            $result = $cp->evaluate($item, $user);
            return [
                'id' => $cp->id(),
                'label' => $cp->label(),
                'passed' => $result->passed,
                'reason' => $result->reason,
                'progress_pct' => $result->progressPct,
            ];
        }, $this->checkpoints);
    }

    public function allPassed(WishlistItem $item, User $user): bool
    {
        foreach ($this->checkpoints as $cp) {
            if ($cp->evaluate($item, $user)->passed !== true) {
                return false;
            }
        }
        return true;
    }
}
