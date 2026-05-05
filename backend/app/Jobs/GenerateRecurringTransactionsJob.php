<?php

namespace App\Jobs;

use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateRecurringTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function handle(RecurringTransactionService $service): void
    {
        RecurringTransaction::where('active', true)
            ->cursor()
            ->each(function (RecurringTransaction $rt) use ($service) {
                $service->generateForMonth($rt);
            });
    }
}
