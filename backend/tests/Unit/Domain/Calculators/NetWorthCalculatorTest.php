<?php

namespace Tests\Unit\Domain\Calculators;

use App\Domain\Calculators\NetWorthCalculator;
use App\Models\Account;
use App\Models\NetWorthSnapshot;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetWorthCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private NetWorthCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new NetWorthCalculator();
    }

    public function test_empty_user_returns_zero(): void
    {
        $user = User::factory()->create();

        $result = $this->calc->calculate($user);

        $this->assertSame('0.00', $result['total']);
        $this->assertSame([], $result['by_account']);
    }

    public function test_basic_calculation_without_snapshot(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '0.00']);

        Transaction::factory()->for($user)->for($account)->income()->create([
            'amount'      => '1000.00',
            'occurred_on' => now()->subMonth()->toDateString(),
        ]);
        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount'      => '300.00',
            'occurred_on' => now()->subMonth()->toDateString(),
        ]);

        $result = $this->calc->calculate($user);

        $this->assertSame('700.00', $result['total']);
        $this->assertSame('700.00', $result['by_account'][0]['balance']);
    }

    public function test_soft_deleted_transactions_are_excluded(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '0.00']);

        Transaction::factory()->for($user)->for($account)->income()->create([
            'amount'      => '500.00',
            'occurred_on' => now()->subMonth()->toDateString(),
        ]);
        Transaction::factory()->for($user)->for($account)->income()->create([
            'amount'      => '999.00',
            'occurred_on' => now()->subMonth()->toDateString(),
            'deleted_at'  => now(),
        ]);

        $result = $this->calc->calculate($user);

        $this->assertSame('500.00', $result['total']);
    }

    public function test_archived_accounts_are_excluded(): void
    {
        $user    = User::factory()->create();
        $active  = Account::factory()->for($user)->create(['initial_balance' => '200.00']);
        $archived = Account::factory()->for($user)->archived()->create(['initial_balance' => '9999.00']);

        $result = $this->calc->calculate($user);

        $this->assertSame('200.00', $result['total']);
        $this->assertCount(1, $result['by_account']);
    }

    public function test_uses_snapshot_plus_current_month_delta(): void
    {
        $user    = User::factory()->create(['timezone' => 'America/Sao_Paulo']);
        $account = Account::factory()->for($user)->create(['initial_balance' => '0.00']);

        $lastMonthEnd = now('America/Sao_Paulo')->subMonth()->endOfMonth()->toDateString();

        NetWorthSnapshot::create([
            'user_id'          => $user->id,
            'captured_on'      => $lastMonthEnd,
            'total_assets'     => '800.00',
            'total_by_account' => [(string) $account->id => '800.00'],
            'monthly_income'   => '1000.00',
            'monthly_expenses' => '200.00',
            'savings_rate'     => '80.00',
            'created_at'       => now(),
        ]);

        // Current-month transaction only
        Transaction::factory()->for($user)->for($account)->income()->create([
            'amount'      => '150.00',
            'occurred_on' => now()->startOfMonth()->toDateString(),
        ]);

        $result = $this->calc->calculate($user);

        $this->assertSame('950.00', $result['total']);
    }

    public function test_initial_balance_included_in_calculation(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '500.00']);

        $result = $this->calc->calculate($user);

        $this->assertSame('500.00', $result['total']);
    }
}
