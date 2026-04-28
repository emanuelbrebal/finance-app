<?php

namespace App\Domain\Calculators;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SavingsRateCalculator
{
    /**
     * Calculate savings rate for a given month (YYYY-MM).
     *
     * savings_rate = (income - expenses) / income * 100
     * Returns null when there is no income (avoids division by zero).
     */
    public function forMonth(User $user, string $month): array
    {
        $start = $month.'-01';
        $end   = date('Y-m-t', strtotime($start)); // last day of month

        $rows = DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('out_of_scope', false)
            ->whereBetween('occurred_on', [$start, $end])
            ->select(
                'direction',
                DB::raw('SUM(amount) as total'),
            )
            ->groupBy('direction')
            ->get()
            ->keyBy('direction');

        $income   = (float) ($rows->get('in')?->total  ?? 0);
        $expenses = (float) ($rows->get('out')?->total ?? 0);
        $saved    = $income - $expenses;

        return [
            'income'       => number_format($income, 2, '.', ''),
            'expenses'     => number_format($expenses, 2, '.', ''),
            'saved'        => number_format($saved, 2, '.', ''),
            'savings_rate' => $income > 0
                ? round($saved / $income * 100, 1)
                : null,
        ];
    }
}
