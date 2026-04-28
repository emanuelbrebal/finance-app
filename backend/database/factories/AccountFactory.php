<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Nubank', 'Itaú', 'Carteira', 'Inter', 'Poupança']),
            'type' => fake()->randomElement(['checking', 'savings', 'credit_card', 'cash', 'investment']),
            'initial_balance' => fake()->randomFloat(2, 0, 10000),
            'currency' => 'BRL',
            'color' => fake()->hexColor(),
            'icon' => null,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['archived_at' => now()]);
    }
}
