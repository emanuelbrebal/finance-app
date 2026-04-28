<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_cannot_access(): void
    {
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    }

    public function test_returns_expected_shape(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard');

        $response->assertOk()
            ->assertJsonStructure(['data' => [
                'net_worth',
                'net_worth_by_account',
                'month',
                'income',
                'expenses',
                'saved',
                'savings_rate',
                'burn_rate_3m',
                'runway_months',
                'top_expenses',
                'recent_transactions',
            ]]);
    }

    public function test_net_worth_adds_initial_balance_and_transactions(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '1000.00']);

        Transaction::factory()->for($user)->for($account)->create([
            'direction' => 'in', 'amount' => '500.00',
            'occurred_on' => now()->toDateString(), 'dedup_hash' => fake()->sha256(),
        ]);
        Transaction::factory()->for($user)->for($account)->create([
            'direction' => 'out', 'amount' => '200.00',
            'occurred_on' => now()->toDateString(), 'dedup_hash' => fake()->sha256(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard');

        // 1000 + 500 - 200 = 1300
        $response->assertJsonPath('data.net_worth', '1300.00');
    }

    public function test_savings_rate_current_month(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '0']);

        Transaction::factory()->for($user)->for($account)->create([
            'direction' => 'in', 'amount' => '4000.00',
            'occurred_on' => now()->startOfMonth()->toDateString(),
            'dedup_hash' => fake()->sha256(),
        ]);
        Transaction::factory()->for($user)->for($account)->create([
            'direction' => 'out', 'amount' => '1000.00',
            'occurred_on' => now()->startOfMonth()->toDateString(),
            'dedup_hash' => fake()->sha256(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard');

        $response->assertJsonPath('data.income', '4000.00')
            ->assertJsonPath('data.expenses', '1000.00')
            ->assertJsonPath('data.saved', '3000.00')
            ->assertJsonPath('data.savings_rate', 75);
    }

    public function test_top_expenses_limited_to_5(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '0']);

        $categories = Category::factory()->count(6)->for($user)->expense()->create();

        foreach ($categories as $i => $cat) {
            Transaction::factory()->for($user)->for($account)->create([
                'direction'   => 'out',
                'amount'      => (string) (($i + 1) * 100) . '.00',
                'category_id' => $cat->id,
                'occurred_on' => now()->startOfMonth()->toDateString(),
                'dedup_hash'  => fake()->sha256(),
            ]);
        }

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard');

        $this->assertCount(5, $response->json('data.top_expenses'));
    }

    public function test_recent_transactions_limited_to_5(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '0']);

        Transaction::factory()->count(8)->for($user)->for($account)->create([
            'dedup_hash' => fn () => fake()->sha256(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard');

        $this->assertCount(5, $response->json('data.recent_transactions'));
    }

    public function test_only_own_data_returned(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();
        $acct  = Account::factory()->for($other)->create(['initial_balance' => '9999']);

        Transaction::factory()->for($other)->for($acct)->create([
            'direction' => 'in', 'amount' => '5000.00',
            'occurred_on' => now()->toDateString(), 'dedup_hash' => fake()->sha256(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard');

        $response->assertJsonPath('data.net_worth', '0.00');
        $this->assertEmpty($response->json('data.recent_transactions'));
    }
}
