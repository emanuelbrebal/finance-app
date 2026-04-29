<?php

namespace App\Providers;

use App\Domain\Categorization\CategorizationRuleApplier;
use App\Domain\Importers\ImporterRegistry;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Policies\AccountPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ImporterRegistry::class, function ($app) {
            $importers = array_map(
                fn (string $cls) => $app->make($cls),
                config('importers.importers', []),
            );
            return new ImporterRegistry($importers);
        });

        $this->app->singleton(CategorizationRuleApplier::class);
    }

    public function boot(): void
    {
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
    }
}
