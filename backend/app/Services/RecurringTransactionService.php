<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringTransactionService
{
    /**
     * Generate a single transaction for the given recurring template
     * for the current month (or referenced month). Idempotent: skips if
     * already generated for that month.
     *
     * Returns the created Transaction or null if already generated/inactive/out of range.
     */
    public function generateForMonth(RecurringTransaction $recurring, ?Carbon $referenceMonth = null): ?Transaction
    {
        $month = ($referenceMonth ?? now())->copy()->startOfMonth();

        if (!$recurring->active) {
            return null;
        }

        if ($recurring->starts_on && $month->lt(Carbon::parse($recurring->starts_on)->startOfMonth())) {
            return null;
        }

        if ($recurring->ends_on && $month->gt(Carbon::parse($recurring->ends_on)->startOfMonth())) {
            return null;
        }

        $occurredOn = $this->resolveOccurredOn($recurring->day_of_month, $month);

        if ($recurring->last_generated_on
            && Carbon::parse($recurring->last_generated_on)->isSameMonth($month)) {
            return null;
        }

        $amount = number_format((float) $recurring->amount, 2, '.', '');
        $hash = Transaction::computeHash(
            $occurredOn->format('Y-m-d'),
            $amount,
            $recurring->direction,
            $recurring->description,
            $recurring->account_id,
        );

        // Skip if a duplicate already exists
        if (Transaction::withTrashed()->where('user_id', $recurring->user_id)
            ->where('dedup_hash', $hash)->exists()) {
            $recurring->update(['last_generated_on' => $occurredOn->toDateString()]);
            return null;
        }

        return DB::transaction(function () use ($recurring, $occurredOn, $hash, $amount) {
            $tx = Transaction::create([
                'user_id' => $recurring->user_id,
                'account_id' => $recurring->account_id,
                'category_id' => $recurring->category_id,
                'recurring_transaction_id' => $recurring->id,
                'occurred_on' => $occurredOn->toDateString(),
                'description' => $recurring->description,
                'amount' => $amount,
                'direction' => $recurring->direction,
                'dedup_hash' => $hash,
            ]);

            $recurring->update(['last_generated_on' => $occurredOn->toDateString()]);

            return $tx;
        });
    }

    private function resolveOccurredOn(int $dayOfMonth, Carbon $month): Carbon
    {
        $lastDay = $month->copy()->endOfMonth()->day;
        $day = min($dayOfMonth, $lastDay);
        return $month->copy()->setDay($day);
    }
}
