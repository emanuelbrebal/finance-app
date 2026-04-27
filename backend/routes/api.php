<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    $checks = [
        'app' => 'ok',
        'database' => rescue(fn () => DB::connection()->getPdo() ? 'ok' : 'fail', 'fail', false),
        'redis' => rescue(fn () => Redis::ping() ? 'ok' : 'fail', 'fail', false),
    ];

    return response()->json([
        'data' => [
            'status' => in_array('fail', $checks, true) ? 'degraded' : 'ok',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ],
    ]);
});
