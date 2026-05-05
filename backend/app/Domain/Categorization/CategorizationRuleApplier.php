<?php

namespace App\Domain\Categorization;

use App\Models\CategorizationRule;
use App\Models\User;

class CategorizationRuleApplier
{
    public function suggest(string $description, User $user): ?int
    {
        $rules = CategorizationRule::where('user_id', $user->id)
            ->orderByDesc('priority')
            ->orderByDesc('hits')
            ->get();

        foreach ($rules as $rule) {
            if ($this->matches($description, $rule)) {
                $rule->increment('hits');
                return $rule->category_id;
            }
        }

        return null;
    }

    private function matches(string $description, CategorizationRule $rule): bool
    {
        $haystack = mb_strtolower($description);
        $needle = mb_strtolower($rule->pattern);

        return match ($rule->match_type) {
            'contains' => str_contains($haystack, $needle),
            'starts_with' => str_starts_with($haystack, $needle),
            'exact' => $haystack === $needle,
            'regex' => (bool) @preg_match("/{$rule->pattern}/i", $description),
            default => false,
        };
    }
}
