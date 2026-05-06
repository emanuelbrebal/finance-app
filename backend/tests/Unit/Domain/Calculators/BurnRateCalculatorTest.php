<?php

namespace Tests\Unit\Domain\Calculators;

use App\Domain\Calculators\BurnRateCalculator;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BurnRateCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private BurnRateCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new BurnRateCalculator();
    }

    public function test_empty_user_returns_zero(): void
    {
        $user = User::factory()->create();

        $result = $this->calc->lastMonths($user, 3);

        $this->assertSame('0.00', $result['burn_rate']);
        $this->assertSame(0, $result['months_sampled']);
    }

    public function test_averages_expense_months(): void
    {
        $user    = User::factory()->create(['timezone' => 'America/Sao_Paulo']);
        $account = Account::factory()->for($user)->create();

        // 2 complete months of expenses
        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount'      => '600.00',
            'occurred_on' => now('America/Sao_Paulo')->subMonths(2)->startOfMonth()->toDateString(),
        ]);
        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount'      => '400.00',
            'occurred_on' => now('America/Sao_Paulo')->subMonth()->startOfMonth()->toDateString(),
        ]);

        $result = $this->calc->lastMonths($user, 3);

        $this->assertSame('500.00', $result['burn_rate']);
        $this->assertSame(2, $result['months_sampled']);
    }

    public function test_current_month_excluded(): void
    {
        $user    = User::factory()->create(['timezone' => 'America/Sao_Paulo']);
        $account = Account::factory()->for($user)->create();

        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount'      => '9999.00',
            'occurred_on' => now()->startOfMonth()->toDateString(),
        ]);

        $result = $this->calc->lastMonths($user, 3);

        $this->assertSame('0.00', $result['burn_rate']);
        $this->assertSame(0, $result['months_sampled']);
    }

    public function test_out_of_scope_excluded(): void
    {
        $user    = User::factory()->create(['timezone' => 'America/Sao_Paulo']);
        $account = Account::factory()->for($user)->create();

        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount'       => '500.00',
            'occurred_on'  => now()->subMonth()->startOfMonth()->toDateString(),
            'out_of_scope' => true,
        ]);

        $result = $this->calc->lastMonths($user, 3);

        $this->assertSame('0.00', $result['burn_rate']);
    }

    public function test_soft_deleted_transactions_excluded(): void
    {
        $user    = User::factory()->create(['timezone' => 'America/Sao_Paulo']);
        $account = Account::factory()->for($user)->create();

        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount'      => '999.00',
            'occurred_on' => now()->subMonth()->startOfMonth()->toDateString(),
            'deleted_at'  => now(),
        ]);

        $result = $this->calc->lastMonths($user, 3);

        $this->assertSame('0.00', $result['burn_rate']);
    }
}
