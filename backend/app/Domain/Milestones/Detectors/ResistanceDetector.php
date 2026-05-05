<?php

namespace App\Domain\Milestones\Detectors;

use App\Domain\Milestones\Contracts\MilestoneDetector;
use App\Domain\Milestones\DTOs\MilestoneDTO;
use App\Models\Milestone;
use App\Models\User;
use App\Models\WishlistItem;

class ResistanceDetector implements MilestoneDetector
{
    public function detect(User $user): array
    {
        $existing = Milestone::where('user_id', $user->id)
            ->where('type', 'like', 'resistance_%')
            ->pluck('dedup_key')
            ->all();

        $milestones = [];

        // Items active for 30+ / 60+ days
        $activeItems = WishlistItem::where('user_id', $user->id)
            ->whereIn('status', [WishlistItem::STATUS_WAITING, WishlistItem::STATUS_READY_TO_BUY])
            ->get();

        foreach ($activeItems as $item) {
            $days = (int) $item->created_at->diffInDays(now());

            if ($days >= 30) {
                $dedup = "resistance_item_{$item->id}_30d";
                if (!in_array($dedup, $existing, true)) {
                    $milestones[] = new MilestoneDTO(
                        type: $dedup,
                        tier: 'small',
                        title: "30 dias resistindo a \"{$item->name}\"",
                        body: 'A quarentena fortalece a clareza. Continue.',
                        dedupKey: $dedup,
                        payload: ['wishlist_item_id' => $item->id, 'days' => $days],
                    );
                }
            }

            if ($days >= 60) {
                $dedup = "resistance_item_{$item->id}_60d";
                if (!in_array($dedup, $existing, true)) {
                    $milestones[] = new MilestoneDTO(
                        type: $dedup,
                        tier: 'medium',
                        title: "60 dias na wishlist: \"{$item->name}\"",
                        body: 'Dois meses sem comprar por impulso.',
                        dedupKey: $dedup,
                        payload: ['wishlist_item_id' => $item->id, 'days' => $days],
                    );
                }
            }
        }

        // Items abandoned after 60+ days = big resistance win
        $abandoned = WishlistItem::where('user_id', $user->id)
            ->where('status', WishlistItem::STATUS_ABANDONED)
            ->whereNotNull('abandoned_at')
            ->get();

        foreach ($abandoned as $item) {
            $daysBeforeAbandon = (int) $item->created_at->diffInDays($item->abandoned_at);
            if ($daysBeforeAbandon < 60) continue;

            $dedup = "resistance_abandon_{$item->id}";
            if (in_array($dedup, $existing, true)) continue;

            $milestones[] = new MilestoneDTO(
                type: $dedup,
                tier: 'large',
                title: "Você resistiu a \"{$item->name}\"!",
                body: 'Deixou de gastar R$ ' . number_format((float) $item->target_price, 2, ',', '.') . " em algo que reconheceu não ser essencial.",
                dedupKey: $dedup,
                payload: ['wishlist_item_id' => $item->id, 'amount_saved' => (string) $item->target_price],
            );
        }

        return $milestones;
    }
}
