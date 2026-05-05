<?php

namespace Tests\Unit\Domain\Calculators;

use App\Domain\Calculators\SavingsRateCalculator;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsRateCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private SavingsRateCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new SavingsRateCalculator();
    }

    public function test_no_income_returns_null_savings_rate(): void
    {
        $user = User::factory()->create();

        $result = $this->calc->forMonth($user, '2026-04');

        $this->assertSame('0.00', $result['income']);
        $this->assertNull($result['savings_rate']);
    }

    public function test_calculates_rate_correctly(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        Transaction::factory()->for($user)->for($account)->income()->create([
            'amount' => '2000.00', 'occurred_on' => '2026-04-10',
        ]);
        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount' => '1000.00', 'occurred_on' => '2026-04-15',
        ]);

        $result = $this->calc->forMonth($user, '2026-04');

        $this->assertSame('2000.00', $result['income']);
        $this->assertSame('1000.00', $result['expenses']);
        $this->assertSame('1000.00', $result['saved']);
        $this->assertSame(50.0, $result['savings_rate']);
    }

    public function test_out_of_scope_excluded(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        Transaction::factory()->for($user)->for($account)->income()->create([
            'amount' => '1000.00', 'occurred_on' => '2026-04-10',
        ]);
        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount'       => '9999.00',
            'occurred_on'  => '2026-04-15',
            'out_of_scope' => true,
        ]);

        $result = $this->calc->forMonth($user, '2026-04');

        $this->assertSame('0.00', $result['expenses']);
        $this->assertSame(100.0, $result['savings_rate']);
    }

    public function test_soft_deleted_excluded(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        Transaction::factory()->for($user)->for($account)->income()->create([
            'amount' => '1000.00', 'occurred_on' => '2026-04-10',
        ]);
        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount'      => '500.00',
            'occurred_on' => '2026-04-15',
            'deleted_at'  => now(),
        ]);

        $result = $this->calc->forMonth($user, '2026-04');

        $this->assertSame('0.00', $result['expenses']);
    }

    public function test_negative_savings_rate_when_expenses_exceed_income(): void
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        Transaction::factory()->for($user)->for($account)->income()->create([
            'amount' => '1000.00', 'occurred_on' => '2026-04-10',
        ]);
        Transaction::factory()->for($user)->for($account)->expense()->create([
            'amount' => '1500.00', 'occurred_on' => '2026-04-15',
        ]);

        $result = $this->calc->forMonth($user, '2026-04');

        $this->assertSame('-500.00', $result['saved']);
        $this->assertSame(-50.0, $result['savings_rate']);
    }
}
