<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\StreakService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateStreaksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    /**
     * @param  string  $kind  'weekly' (just-closed week) or 'monthly' (just-closed month)
     */
    public function __construct(public readonly string $kind = 'weekly') {}

    public function handle(StreakService $service): void
    {
        User::cursor()->each(function (User $user) use ($service) {
            if ($this->kind === 'monthly') {
                $service->updatePositiveMonths($user);
            } else {
                $service->updateWeeklyLogging($user);
            }
        });
    }
}
