<?php

namespace App\Domain\Calculators;

use App\Models\NetWorthSnapshot;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NetWorthCalculator
{
    /**
     * Calculate current net worth using the latest snapshot + current-month delta.
     * Falls back to a full history scan when no snapshot exists yet (new users).
     */
    public function calculate(User $user): array
    {
        $tz = $user->timezone;
        $startOfMonth = now($tz)->startOfMonth()->toDateString();

        $accounts = $user->accounts()
            ->active()
            ->select('id', 'name', 'type', 'color', 'initial_balance', 'currency')
            ->get();

        if ($accounts->isEmpty()) {
            return ['total' => '0.00', 'by_account' => []];
        }

        $accountIds = $accounts->pluck('id');

        $snapshot = NetWorthSnapshot::where('user_id', $user->id)
            ->where('captured_on', '<', $startOfMonth)
            ->orderByDesc('captured_on')
            ->first();

        $snapshotByAccount = $snapshot
            ? collect($snapshot->total_by_account)
            : collect();

        $accountsNeedingFullCalc = $accountIds->diff(
            $snapshotByAccount->keys()->map(fn ($k) => (int) $k)
        );

        // Current-month delta for every active account
        $currentMonthDeltas = DB::table('transactions')
            ->whereIn('account_id', $accountIds)
            ->whereNull('deleted_at')
            ->where('occurred_on', '>=', $startOfMonth)
            ->select(
                'account_id',
                DB::raw("SUM(CASE WHEN direction='in' THEN amount ELSE 0 END) AS delta_in"),
                DB::raw("SUM(CASE WHEN direction='out' THEN amount ELSE 0 END) AS delta_out"),
            )
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        // Pre-snapshot history only for accounts not covered by the snapshot
        $fullHistory = $accountsNeedingFullCalc->isNotEmpty()
            ? DB::table('transactions')
                ->whereIn('account_id', $accountsNeedingFullCalc)
                ->whereNull('deleted_at')
                ->where('occurred_on', '<', $startOfMonth)
                ->select(
                    'account_id',
                    DB::raw("SUM(CASE WHEN direction='in' THEN amount ELSE 0 END) AS total_in"),
                    DB::raw("SUM(CASE WHEN direction='out' THEN amount ELSE 0 END) AS total_out"),
                )
                ->groupBy('account_id')
                ->get()
                ->keyBy('account_id')
            : collect();

        $byAccount = [];
        $total = '0.00';

        foreach ($accounts as $account) {
            $d = $currentMonthDeltas->get($account->id);
            $deltaIn  = $d ? (string) $d->delta_in  : '0.00';
            $deltaOut = $d ? (string) $d->delta_out : '0.00';

            if ($snapshotByAccount->has((string) $account->id)) {
                $base = (string) $snapshotByAccount->get((string) $account->id);
            } else {
                $h = $fullHistory->get($account->id);
                $histIn  = $h ? (string) $h->total_in  : '0.00';
                $histOut = $h ? (string) $h->total_out : '0.00';
                $base = bcsub(bcadd((string) $account->initial_balance, $histIn, 2), $histOut, 2);
            }

            $balance = bcsub(bcadd($base, $deltaIn, 2), $deltaOut, 2);
            $total   = bcadd($total, $balance, 2);

            $byAccount[] = [
                'account_id' => $account->id,
                'name'       => $account->name,
                'type'       => $account->type,
                'color'      => $account->color,
                'balance'    => $balance,
            ];
        }

        return [
            'total'      => $total,
            'by_account' => $byAccount,
        ];
    }
}
