<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class DefaultCategoriesService
{
    /**
     * Default categories for Brazilian users. Idempotent: skips
     * any (user_id, name) pair that already exists.
     *
     * Note: 'Outros' appears in both kinds — the income variant gets
     * the suffix " (entrada)" because of the unique(user_id, name)
     * constraint.
     */
    public const DEFAULTS = [
        // Despesas (is_essential = false marca "supérfluo" pra regras de consciência)
        ['name' => 'Alimentação',   'kind' => Category::KIND_EXPENSE, 'icon' => 'utensils',    'color' => '#f97316', 'is_essential' => true],
        ['name' => 'Moradia',       'kind' => Category::KIND_EXPENSE, 'icon' => 'home',        'color' => '#0ea5e9', 'is_essential' => true],
        ['name' => 'Transporte',    'kind' => Category::KIND_EXPENSE, 'icon' => 'car',         'color' => '#22c55e', 'is_essential' => true],
        ['name' => 'Lazer',         'kind' => Category::KIND_EXPENSE, 'icon' => 'gamepad',     'color' => '#a855f7', 'is_essential' => false],
        ['name' => 'Assinaturas',   'kind' => Category::KIND_EXPENSE, 'icon' => 'repeat',      'color' => '#ec4899', 'is_essential' => false],
        ['name' => 'Saúde',         'kind' => Category::KIND_EXPENSE, 'icon' => 'heart',       'color' => '#ef4444', 'is_essential' => true],
        ['name' => 'Educação',      'kind' => Category::KIND_EXPENSE, 'icon' => 'book',        'color' => '#6366f1', 'is_essential' => true],
        ['name' => 'Investimento',  'kind' => Category::KIND_EXPENSE, 'icon' => 'trending-up', 'color' => '#14b8a6', 'is_essential' => true],
        ['name' => 'Outros',        'kind' => Category::KIND_EXPENSE, 'icon' => 'circle',      'color' => '#94a3b8', 'is_essential' => true],

        // Entradas
        ['name' => 'Renda Principal',   'kind' => Category::KIND_INCOME, 'icon' => 'briefcase', 'color' => '#10b981', 'is_essential' => true],
        ['name' => 'Renda Extra',       'kind' => Category::KIND_INCOME, 'icon' => 'plus',      'color' => '#84cc16', 'is_essential' => true],
        ['name' => 'Outros (entrada)',  'kind' => Category::KIND_INCOME, 'icon' => 'circle',    'color' => '#94a3b8', 'is_essential' => true],
    ];

    /**
     * Seed defaults for the given user. Idempotent — only inserts
     * categories whose (user_id, name) is missing.
     *
     * @return int number of categories created
     */
    public function seedFor(User $user): int
    {
        $existingNames = $user->categories()
            ->pluck('name')
            ->all();

        $created = 0;
        foreach (self::DEFAULTS as $defaults) {
            if (in_array($defaults['name'], $existingNames, true)) {
                continue;
            }

            $user->categories()->create($defaults);
            $existingNames[] = $defaults['name'];
            $created++;
        }

        return $created;
    }
}
