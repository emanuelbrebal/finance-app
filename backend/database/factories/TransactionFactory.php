<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $occurredOn = fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d');
        $amount = fake()->randomFloat(2, 1, 5000);
        $direction = fake()->randomElement([Transaction::DIRECTION_IN, Transaction::DIRECTION_OUT]);
        $description = fake()->sentence(3);
        $accountId = Account::factory();

        return [
            'user_id' => User::factory(),
            'account_id' => $accountId,
            'category_id' => null,
            'occurred_on' => $occurredOn,
            'description' => $description,
            'amount' => $amount,
            'direction' => $direction,
            'notes' => null,
            'tags' => [],
            'out_of_scope' => false,
            'dedup_hash' => fn (array $attrs) => Transaction::computeHash(
                $attrs['occurred_on'],
                (string) $attrs['amount'],
                $attrs['direction'],
                $attrs['description'],
                is_int($attrs['account_id']) ? $attrs['account_id'] : 1,
            ),
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => ['direction' => Transaction::DIRECTION_IN]);
    }

    public function expense(): static
    {
        return $this->state(fn () => ['direction' => Transaction::DIRECTION_OUT]);
    }
}
