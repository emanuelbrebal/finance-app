<?php

namespace App\Domain\Milestones\Detectors;

use App\Domain\Calculators\NetWorthCalculator;
use App\Domain\Milestones\Contracts\MilestoneDetector;
use App\Domain\Milestones\DTOs\MilestoneDTO;
use App\Models\Milestone;
use App\Models\User;

class NetWorthDetector implements MilestoneDetector
{
    private const THRESHOLDS = [
        1000 => ['tier' => 'small', 'label' => 'R$ 1.000'],
        5000 => ['tier' => 'medium', 'label' => 'R$ 5.000'],
        10000 => ['tier' => 'medium', 'label' => 'R$ 10.000'],
        25000 => ['tier' => 'large', 'label' => 'R$ 25.000'],
        50000 => ['tier' => 'large', 'label' => 'R$ 50.000'],
        75000 => ['tier' => 'large', 'label' => 'R$ 75.000'],
        100000 => ['tier' => 'epic', 'label' => 'R$ 100.000'],
    ];

    public function __construct(private readonly NetWorthCalculator $netWorth) {}

    public function detect(User $user): array
    {
        $netWorth = (float) $this->netWorth->total($user);
        $existing = Milestone::where('user_id', $user->id)
            ->where('type', 'like', 'net_worth_%')
            ->pluck('dedup_key')
            ->all();

        $milestones = [];

        foreach (self::THRESHOLDS as $threshold => $meta) {
            if ($netWorth < $threshold) continue;

            $dedup = "net_worth_{$threshold}";
            if (in_array($dedup, $existing, true)) continue;

            $milestones[] = new MilestoneDTO(
                type: "net_worth_{$threshold}",
                tier: $meta['tier'],
                title: "Você atingiu {$meta['label']}!",
                body: "Patrimônio total ultrapassou {$meta['label']}. Continue o ritmo.",
                dedupKey: $dedup,
                payload: ['threshold' => $threshold, 'net_worth' => number_format($netWorth, 2, '.', '')],
            );
        }

        // Each R$ 25k above R$ 100k
        if ($netWorth >= 100000) {
            $bracket = (int) (floor($netWorth / 25000) * 25000);
            if ($bracket > 100000) {
                $dedup = "net_worth_{$bracket}";
                if (!in_array($dedup, $existing, true)) {
                    $milestones[] = new MilestoneDTO(
                        type: "net_worth_{$bracket}",
                        tier: 'epic',
                        title: 'Patrimônio R$ ' . number_format($bracket, 0, '', '.') . '!',
                        body: 'Mais um ciclo de R$ 25k completado.',
                        dedupKey: $dedup,
                        payload: ['threshold' => $bracket],
                    );
                }
            }
        }

        return $milestones;
    }
}
