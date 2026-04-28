<?php

namespace App\Domain\Calculators;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NetWorthCalculator
{
    /**
     * Calculate current net worth for a user.
     *
     * Net worth = SUM over active accounts of:
     *   initial_balance
     *   + SUM(amount WHERE direction='in' AND deleted_at IS NULL)
     *   - SUM(amount WHERE direction='out' AND deleted_at IS NULL)
     *
     * Returns total and per-account breakdown.
     */
    public function calculate(User $user): array
    {
        $accounts = $user->accounts()
            ->active()
            ->select('id', 'name', 'type', 'color', 'initial_balance', 'currency')
            ->get();

        if ($accounts->isEmpty()) {
            return ['total' => '0.00', 'by_account' => []];
        }

        $accountIds = $accounts->pluck('id');

        // One query: aggregate transactions per account
        $balances = DB::table('transactions')
            ->whereIn('account_id', $accountIds)
            ->whereNull('deleted_at')
            ->select(
                'account_id',
                DB::raw("SUM(CASE WHEN direction = 'in'  THEN amount ELSE 0 END) AS total_in"),
                DB::raw("SUM(CASE WHEN direction = 'out' THEN amount ELSE 0 END) AS total_out"),
            )
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $byAccount = [];
        $total = 0;

        foreach ($accounts as $account) {
            $b = $balances->get($account->id);
            $in  = $b ? (float) $b->total_in  : 0;
            $out = $b ? (float) $b->total_out : 0;
            $balance = (float) $account->initial_balance + $in - $out;
            $total += $balance;

            $byAccount[] = [
                'account_id' => $account->id,
                'name'       => $account->name,
                'type'       => $account->type,
                'color'      => $account->color,
                'balance'    => number_format($balance, 2, '.', ''),
            ];
        }

        return [
            'total'      => number_format($total, 2, '.', ''),
            'by_account' => $byAccount,
        ];
    }
}
