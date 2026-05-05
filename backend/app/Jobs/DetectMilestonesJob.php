<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MilestoneService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectMilestonesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function handle(MilestoneService $service): void
    {
        User::cursor()->each(function (User $user) use ($service) {
            $service->detectFor($user);
        });
    }
}
