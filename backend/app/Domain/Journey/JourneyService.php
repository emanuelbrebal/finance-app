<?php

namespace App\Domain\Journey;

use App\Domain\Calculators\NetWorthCalculator;
use App\Models\User;

class JourneyService
{
    public function __construct(private readonly NetWorthCalculator $netWorth) {}

    /**
     * Resolve the level key for the given net worth value.
     */
    public function resolveLevel(float $netWorth): string
    {
        $levels = config('journey.levels', []);
        foreach ($levels as $key => $cfg) {
            $min = (float) $cfg['min'];
            $max = $cfg['max'] !== null ? (float) $cfg['max'] : INF;
            if ($netWorth >= $min && $netWorth < $max) {
                return $key;
            }
        }
        return array_key_first($levels);
    }

    /**
     * Full state for the user including current level metadata and the next.
     */
    public function stateFor(User $user): array
    {
        $netWorth = (float) $this->netWorth->total($user);
        $currentKey = $this->resolveLevel($netWorth);
        $levels = config('journey.levels', []);
        $keys = array_keys($levels);
        $currentIdx = array_search($currentKey, $keys, true);
        $nextKey = $keys[$currentIdx + 1] ?? null;

        $current = $levels[$currentKey];

        $state = [
            'current_level' => $currentKey,
            'current_level_label' => $current['label'],
            'current_level_icon' => $current['icon'],
            'current_level_min' => (string) $current['min'],
            'current_level_max' => $current['max'] !== null ? (string) $current['max'] : null,
            'net_worth' => number_format($netWorth, 2, '.', ''),
        ];

        if ($nextKey) {
            $next = $levels[$nextKey];
            $state['next_level'] = $nextKey;
            $state['next_level_label'] = $next['label'];
            $state['next_level_threshold'] = (string) $next['min'];
            $state['remaining_to_next'] = number_format(max(0, (float) $next['min'] - $netWorth), 2, '.', '');
            $state['progress_pct'] = round(
                (($netWorth - (float) $current['min']) / max(1, (float) $next['min'] - (float) $current['min'])) * 100,
                1,
            );
        } else {
            $state['next_level'] = null;
            $state['progress_pct'] = 100.0;
        }

        return $state;
    }

    /**
     * Persist current level on the user (used by snapshot job).
     */
    public function persistCurrentLevel(User $user): string
    {
        $netWorth = (float) $this->netWorth->total($user);
        $key = $this->resolveLevel($netWorth);
        if ($user->journey_level !== $key) {
            $user->update(['journey_level' => $key]);
        }
        return $key;
    }
}
