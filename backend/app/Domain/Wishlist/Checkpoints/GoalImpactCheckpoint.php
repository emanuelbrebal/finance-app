<?php

namespace App\Domain\Wishlist\Checkpoints;

use App\Domain\Calculators\NetWorthCalculator;
use App\Domain\Wishlist\Checkpoints\Contracts\CheckpointInterface;
use App\Domain\Wishlist\CheckpointResult;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;

class GoalImpactCheckpoint implements CheckpointInterface
{
    private const MAX_DELAY_DAYS = 30;

    public function __construct(private readonly NetWorthCalculator $netWorth) {}

    public function id(): string
    {
        return 'goal_impact';
    }

    public function label(): string
    {
        return 'Impacto aceitável na meta';
    }

    public function evaluate(WishlistItem $item, User $user): CheckpointResult
    {
        if (!$user->target_net_worth || (float) $user->target_net_worth <= 0) {
            return CheckpointResult::passed('Meta principal não definida — proteção desativada.');
        }

        $netWorth = (float) $this->netWorth->total($user);
        $target = (float) $user->target_net_worth;
        $price = (float) $item->target_price;

        // Average monthly accumulation over last 6 months (income - expenses)
        $start = now()->subMonths(6)->startOfMonth()->toDateString();
        $end = now()->subMonth()->endOfMonth()->toDateString();

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
        $monthlyAccumulation = ($income - $expenses) / 6;

        if ($monthlyAccumulation <= 0) {
            return CheckpointResult::passed(
                'Sem progresso mensurável em direção à meta — sem impacto calculável.',
            );
        }

        // Days = (delta-net-worth) / (monthly-accumulation / 30)
        $delayDays = (int) round(($price / $monthlyAccumulation) * 30);

        if ($delayDays <= self::MAX_DELAY_DAYS) {
            return CheckpointResult::passed(
                "Comprar atrasa sua meta em {$delayDays} dias (limite: " . self::MAX_DELAY_DAYS . ' dias).',
            );
        }

        $pct = max(0, min(100, round((self::MAX_DELAY_DAYS / $delayDays) * 100, 1)));
        return CheckpointResult::failed(
            "Comprar atrasaria sua meta em {$delayDays} dias (limite: " . self::MAX_DELAY_DAYS . ' dias).',
            $pct,
        );
    }
}
