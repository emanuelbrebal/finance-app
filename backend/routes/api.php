<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\CategorizationRuleController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\ImportController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RecurringTransactionController;
use App\Http\Controllers\Api\V1\WishlistController;
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
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::get('/dashboard', DashboardController::class);

    Route::apiResource('accounts', AccountController::class);

    Route::post('/categories/seed', [CategoryController::class, 'seed']);
    Route::apiResource('categories', CategoryController::class);

    Route::get('/transactions/summary', [TransactionController::class, 'summary']);
    Route::post('/transactions/bulk-categorize', [TransactionController::class, 'bulkCategorize']);
    Route::apiResource('transactions', TransactionController::class);

    // Imports
    Route::get('/imports', [ImportController::class, 'index']);
    Route::post('/imports', [ImportController::class, 'store']);
    Route::get('/imports/{import}', [ImportController::class, 'show']);
    Route::get('/imports/{import}/preview', [ImportController::class, 'preview']);
    Route::post('/imports/{import}/confirm', [ImportController::class, 'confirm']);
    Route::post('/imports/{import}/revert', [ImportController::class, 'revert']);

    // Categorization rules
    Route::post('/categorization-rules/{categorizationRule}/apply-to-existing', [CategorizationRuleController::class, 'applyToExisting']);
    Route::apiResource('categorization-rules', CategorizationRuleController::class)->except(['show']);

    // Recurring transactions
    Route::post('/recurring-transactions/{recurringTransaction}/generate-now', [RecurringTransactionController::class, 'generateNow']);
    Route::apiResource('recurring-transactions', RecurringTransactionController::class)->except(['show']);

    // Goals (emergency-fund routes must come before {goal} param to avoid collision)
    Route::get('/goals/emergency-fund', [GoalController::class, 'emergencyFund']);
    Route::post('/goals/emergency-fund/auto-target', [GoalController::class, 'autoTargetEmergencyFund']);
    Route::post('/goals/{goal}/deposit', [GoalController::class, 'deposit']);
    Route::apiResource('goals', GoalController::class);

    // Wishlist (summary route must come before {wishlist} param)
    Route::get('/wishlist/summary', [WishlistController::class, 'summary']);
    Route::post('/wishlist/{wishlist}/extend-quarantine', [WishlistController::class, 'extendQuarantine']);
    Route::post('/wishlist/{wishlist}/abandon', [WishlistController::class, 'abandon']);
    Route::post('/wishlist/{wishlist}/purchase', [WishlistController::class, 'purchase']);
    Route::post('/wishlist/{wishlist}/check-prices', [WishlistController::class, 'checkPrices']);
    Route::apiResource('wishlist', WishlistController::class)->parameters(['wishlist' => 'wishlist']);
});
