<?php

use App\Jobs\GenerateRecurringTransactionsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generate recurring transactions on the 1st of every month at 06:00 (BRT)
Schedule::job(new GenerateRecurringTransactionsJob())
    ->monthlyOn(1, '06:00')
    ->name('generate-recurring-transactions')
    ->withoutOverlapping()
    ->onOneServer();
