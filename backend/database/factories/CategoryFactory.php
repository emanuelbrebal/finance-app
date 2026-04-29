<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'kind' => fake()->randomElement([Category::KIND_INCOME, Category::KIND_EXPENSE]),
            'color' => fake()->hexColor(),
            'icon' => fake()->randomElement(['utensils', 'home', 'car', 'heart', 'book']),
            'is_essential' => fake()->boolean(80),
            'monthly_budget' => null,
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => ['kind' => Category::KIND_INCOME]);
    }

    public function expense(): static
    {
        return $this->state(fn () => ['kind' => Category::KIND_EXPENSE]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['archived_at' => now()]);
    }
}
