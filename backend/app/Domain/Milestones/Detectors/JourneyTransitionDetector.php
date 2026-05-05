<?php

namespace App\Domain\Milestones\Detectors;

use App\Domain\Journey\JourneyService;
use App\Domain\Milestones\Contracts\MilestoneDetector;
use App\Domain\Milestones\DTOs\MilestoneDTO;
use App\Models\Milestone;
use App\Models\User;

class JourneyTransitionDetector implements MilestoneDetector
{
    public function __construct(private readonly JourneyService $journey) {}

    public function detect(User $user): array
    {
        $current = $this->journey->stateFor($user)['current_level'];
        $cached = $user->journey_level;

        if ($current === $cached) {
            return [];
        }

        // Only celebrate transitions UP (don't celebrate going down — life happens)
        $levels = array_keys(config('journey.levels', []));
        if ($cached !== null) {
            $cachedIdx = array_search($cached, $levels, true);
            $currentIdx = array_search($current, $levels, true);
            if ($currentIdx <= $cachedIdx) {
                return [];
            }
        }

        $cfg = config("journey.levels.{$current}");
        $dedup = "journey_transition_{$current}";

        $exists = Milestone::where('user_id', $user->id)
            ->where('dedup_key', $dedup)
            ->exists();

        if ($exists) {
            return [];
        }

        return [
            new MilestoneDTO(
                type: $dedup,
                tier: 'epic',
                title: "Você é {$cfg['icon']} {$cfg['label']}",
                body: 'Transição para o próximo nível da sua jornada financeira.',
                dedupKey: $dedup,
                payload: ['level' => $current, 'icon' => $cfg['icon'], 'label' => $cfg['label']],
            ),
        ];
    }
}
