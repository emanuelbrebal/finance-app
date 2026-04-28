<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\DashboardController;
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

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', DashboardController::class);

    Route::apiResource('accounts', AccountController::class);

    Route::post('/categories/seed', [CategoryController::class, 'seed']);
    Route::apiResource('categories', CategoryController::class);

    Route::get('/transactions/summary', [TransactionController::class, 'summary']);
    Route::post('/transactions/bulk-categorize', [TransactionController::class, 'bulkCategorize']);
    Route::apiResource('transactions', TransactionController::class);
});
