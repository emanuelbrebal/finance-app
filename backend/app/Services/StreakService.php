<?php

namespace App\Services;

use App\Models\Streak;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StreakService
{
    public function updateAllForUser(User $user): void
    {
        $this->updateWeeklyLogging($user);
        $this->updatePositiveMonths($user);
    }

    /** Extends the weekly_logging streak if user logged ≥1 transaction in the just-closed week. */
    public function updateWeeklyLogging(User $user): void
    {
        $weekStart = now()->subWeek()->startOfWeek()->toDateString();
        $weekEnd = now()->subWeek()->endOfWeek()->toDateString();

        $hasActivity = Transaction::where('user_id', $user->id)
            ->whereBetween('occurred_on', [$weekStart, $weekEnd])
            ->exists();

        $streak = Streak::firstOrCreate(
            ['user_id' => $user->id, 'kind' => Streak::KIND_WEEKLY_LOGGING],
            ['current_count' => 0, 'best_count' => 0],
        );

        if ($hasActivity) {
            $streak->current_count += 1;
            $streak->last_extended_on = now()->toDateString();
            $streak->current_started_on = $streak->current_started_on ?? $weekStart;
            $streak->best_count = max($streak->best_count, $streak->current_count);
        } else {
            // Silent reset (no notification, no shame)
            $streak->current_count = 0;
            $streak->current_started_on = null;
        }

        $streak->save();
    }

    /** Extends positive_months streak if just-closed month had income > expenses. */
    public function updatePositiveMonths(User $user): void
    {
        $monthStart = now()->subMonth()->startOfMonth()->toDateString();
        $monthEnd = now()->subMonth()->endOfMonth()->toDateString();

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
        $isPositive = $income > $expenses;

        $streak = Streak::firstOrCreate(
            ['user_id' => $user->id, 'kind' => Streak::KIND_POSITIVE_MONTHS],
            ['current_count' => 0, 'best_count' => 0],
        );

        if ($isPositive) {
            $streak->current_count += 1;
            $streak->last_extended_on = now()->toDateString();
            $streak->current_started_on = $streak->current_started_on ?? $monthStart;
            $streak->best_count = max($streak->best_count, $streak->current_count);
        } else {
            $streak->current_count = 0;
            $streak->current_started_on = null;
        }

        $streak->save();
    }
}
