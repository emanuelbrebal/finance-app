<?php

namespace App\Services;

use App\Domain\Calculators\BurnRateCalculator;
use App\Domain\Calculators\NetWorthCalculator;
use App\Domain\Calculators\SavingsRateCalculator;
use App\Http\Resources\V1\TransactionResource;
use App\Models\User;

class DashboardService
{
    public function __construct(
        private readonly NetWorthCalculator $netWorth,
        private readonly SavingsRateCalculator $savingsRate,
        private readonly BurnRateCalculator $burnRate,
    ) {}

    public function forUser(User $user): array
    {
        $tz           = $user->timezone;
        $currentMonth = now($tz)->format('Y-m');
        $start        = now($tz)->startOfMonth()->toDateString();
        $end          = now($tz)->endOfMonth()->toDateString();

        $netWorth   = $this->netWorth->calculate($user);
        $monthStats = $this->savingsRate->forMonth($user, $currentMonth);
        $burn       = $this->burnRate->lastMonths($user, 3);

        $burnRate = $burn['burn_rate'];
        $runway   = bccomp($burnRate, '0', 2) > 0
            ? round((float) bcdiv($netWorth['total'], $burnRate, 4), 1)
            : null;

        $topExpenses = $user->transactions()
            ->with('category:id,name,color,icon')
            ->whereBetween('occurred_on', [$start, $end])
            ->where('direction', 'out')
            ->where('out_of_scope', false)
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'category_id'    => $row->category_id,
                'category_name'  => $row->category?->name ?? 'Sem categoria',
                'category_color' => $row->category?->color,
                'total'          => number_format((float) $row->total, 2, '.', ''),
                'count'          => $row->count,
            ]);

        $recent = $user->transactions()
            ->with(['account:id,name,color', 'category:id,name,color'])
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return [
            'net_worth'                => $netWorth['total'],
            'net_worth_by_account'     => $netWorth['by_account'],
            'month'                    => $currentMonth,
            'income'                   => $monthStats['income'],
            'expenses'                 => $monthStats['expenses'],
            'saved'                    => $monthStats['saved'],
            'savings_rate'             => $monthStats['savings_rate'],
            'burn_rate_3m'             => $burn['burn_rate'],
            'burn_rate_months_sampled' => $burn['months_sampled'],
            'runway_months'            => $runway,
            'top_expenses'             => $topExpenses,
            'recent_transactions'      => TransactionResource::collection($recent)->resolve(),
        ];
    }
}
