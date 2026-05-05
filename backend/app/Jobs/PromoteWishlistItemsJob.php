<?php

namespace App\Jobs;

use App\Domain\Wishlist\CheckpointEvaluator;
use App\Models\WishlistItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PromoteWishlistItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function handle(CheckpointEvaluator $evaluator): void
    {
        WishlistItem::where('status', WishlistItem::STATUS_WAITING)
            ->with('user')
            ->cursor()
            ->each(function (WishlistItem $item) use ($evaluator) {
                if ($evaluator->allPassed($item, $item->user)) {
                    $item->update(['status' => WishlistItem::STATUS_READY_TO_BUY]);
                }
            });
    }
}
