<?php

namespace App\Domain\Wishlist\Checkpoints;

use App\Domain\Wishlist\Checkpoints\Contracts\CheckpointInterface;
use App\Domain\Wishlist\CheckpointResult;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;

class PositiveSavingsRateCheckpoint implements CheckpointInterface
{
    public function id(): string
    {
        return 'positive_savings_rate';
    }

    public function label(): string
    {
        return 'Mês com poupança positiva';
    }

    public function evaluate(WishlistItem $item, User $user): CheckpointResult
    {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $rows = DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('out_of_scope', false)
            ->whereBetween('occurred_on', [$start, $end])
            ->selectRaw("direction, COALESCE(SUM(amount), 0) as total")
            ->groupBy('direction')
            ->get()
            ->pluck('total', 'direction');

        $income = (float) ($rows['in'] ?? 0);
        $expenses = (float) ($rows['out'] ?? 0);
        $diff = $income - $expenses;

        if ($diff > 0) {
            return CheckpointResult::passed(
                'Esse mês: +R$ ' . number_format($diff, 2, ',', '.') . ' de saldo.',
            );
        }

        return CheckpointResult::failed(
            'Esse mês ainda está negativo (saldo: R$ ' . number_format($diff, 2, ',', '.') . '). Espere virar.',
        );
    }
}
