<?php

namespace App\Domain\Calculators;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class BurnRateCalculator
{
    /**
     * Average monthly expenses over the last N complete months.
     * Excludes the current (partial) month and out_of_scope transactions.
     */
    public function lastMonths(User $user, int $months = 3): array
    {
        $tz    = $user->timezone;
        $start = now($tz)->subMonths($months)->startOfMonth()->toDateString();
        $end   = now($tz)->subMonth()->endOfMonth()->toDateString();

        $rows = DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('out_of_scope', false)
            ->where('direction', 'out')
            ->whereBetween('occurred_on', [$start, $end])
            ->select(
                DB::raw("TO_CHAR(occurred_on, 'YYYY-MM') AS month"),
                DB::raw('SUM(amount) AS total'),
            )
            ->groupBy('month')
            ->get();

        if ($rows->isEmpty()) {
            return [
                'burn_rate'      => '0.00',
                'months_sampled' => 0,
            ];
        }

        $totalExpenses = $rows->reduce(
            fn ($carry, $r) => bcadd($carry, (string) $r->total, 2),
            '0.00'
        );
        $monthCount = $rows->count();

        return [
            'burn_rate'      => bcdiv($totalExpenses, (string) $monthCount, 2),
            'months_sampled' => $monthCount,
        ];
    }
}
