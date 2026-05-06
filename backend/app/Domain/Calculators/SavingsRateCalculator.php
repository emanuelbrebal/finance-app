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
        $end   = date('Y-m-t', strtotime($start));

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

        $income   = (string) ($rows->get('in')?->total  ?? '0.00');
        $expenses = (string) ($rows->get('out')?->total ?? '0.00');
        $saved    = bcsub($income, $expenses, 2);

        return [
            'income'       => $income,
            'expenses'     => $expenses,
            'saved'        => $saved,
            'savings_rate' => bccomp($income, '0', 2) > 0
                ? round((float) bcdiv(bcmul($saved, '100', 4), $income, 4), 1)
                : null,
        ];
    }
}
