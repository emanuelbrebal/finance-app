<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NetWorthSnapshotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CaptureNetWorthSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function handle(NetWorthSnapshotService $service): void
    {
        User::cursor()->each(function (User $user) use ($service) {
            $service->captureForMonth($user);
        });
    }
}
