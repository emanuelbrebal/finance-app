<?php

namespace App\Domain\Wishlist\Checkpoints\Contracts;

use App\Domain\Wishlist\CheckpointResult;
use App\Models\User;
use App\Models\WishlistItem;

interface CheckpointInterface
{
    public function id(): string;

    public function label(): string;

    public function evaluate(WishlistItem $item, User $user): CheckpointResult;
}
