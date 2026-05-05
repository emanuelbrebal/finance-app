<?php

namespace App\Services;

use App\Domain\Calculators\NetWorthCalculator;
use App\Models\Account;
use App\Models\NetWorthSnapshot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NetWorthSnapshotService
{
    public function __construct(private readonly NetWorthCalculator $netWorth) {}

    /**
     * Capture a snapshot for the given month (defaults to last completed month).
     * Idempotent: updates existing row for the same user+captured_on.
     */
    public function captureForMonth(User $user, ?Carbon $month = null): NetWorthSnapshot
    {
        $month = ($month ?? now()->subMonth())->copy();
        $capturedOn = $month->copy()->endOfMonth()->toDateString();
        $monthStart = $month->copy()->startOfMonth()->toDateString();
        $monthEnd = $month->copy()->endOfMonth()->toDateString();

        // Per-account balances
        $totalByAccount = Account::where('user_id', $user->id)
            ->whereNull('archived_at')
            ->get()
            ->mapWithKeys(function (Account $account) use ($capturedOn) {
                $delta = DB::table('transactions')
                    ->where('account_id', $account->id)
                    ->whereNull('deleted_at')
                    ->whereDate('occurred_on', '<=', $capturedOn)
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE 0 END), 0) as inflow,
                        COALESCE(SUM(CASE WHEN direction='out' THEN amount ELSE 0 END), 0) as outflow
                    ")
                    ->first();
                $balance = (float) $account->initial_balance + (float) $delta->inflow - (float) $delta->outflow;
                return [$account->id => number_format($balance, 2, '.', '')];
            })
            ->all();

        $totalAssets = array_sum(array_map('floatval', $totalByAccount));

        // Income / expenses for the month
        $rows = DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('out_of_scope', false)
            ->whereBetween('occurred_on', [$monthStart, $monthEnd])
            ->selectRaw("direction, COALESCE(SUM(amount), 0) as total")
            ->groupBy('direction')
            ->get()
            ->pluck('total', 'direction');

        $income = (float) ($rows['in'] ?? 0);
        $expenses = (float) ($rows['out'] ?? 0);
        $savingsRate = $income > 0 ? round((($income - $expenses) / $income) * 100, 2) : 0;

        return NetWorthSnapshot::updateOrCreate(
            ['user_id' => $user->id, 'captured_on' => $capturedOn],
            [
                'total_assets' => number_format($totalAssets, 2, '.', ''),
                'total_by_account' => $totalByAccount,
                'monthly_income' => number_format($income, 2, '.', ''),
                'monthly_expenses' => number_format($expenses, 2, '.', ''),
                'savings_rate' => number_format($savingsRate, 2, '.', ''),
                'created_at' => now(),
            ],
        );
    }

    /**
     * Backfill snapshots for the last N months for a user (useful on first install
     * or after large historical imports).
     */
    public function backfill(User $user, int $months = 12): int
    {
        $count = 0;
        for ($i = 1; $i <= $months; $i++) {
            $this->captureForMonth($user, now()->subMonths($i));
            $count++;
        }
        return $count;
    }
}
