<?php

namespace App\Domain\Milestones\Detectors;

use App\Domain\Calculators\BurnRateCalculator;
use App\Domain\Milestones\Contracts\MilestoneDetector;
use App\Domain\Milestones\DTOs\MilestoneDTO;
use App\Models\Goal;
use App\Models\Milestone;
use App\Models\NetWorthSnapshot;
use App\Models\User;

class FinancialHealthDetector implements MilestoneDetector
{
    public function __construct(private readonly BurnRateCalculator $burnRate) {}

    public function detect(User $user): array
    {
        $existing = Milestone::where('user_id', $user->id)
            ->where('type', 'like', 'financial_health_%')
            ->pluck('dedup_key')
            ->all();

        $milestones = [];

        // Emergency fund coverage milestones
        $fund = Goal::where('user_id', $user->id)
            ->where('is_emergency_fund', true)
            ->first();

        if ($fund) {
            $burn = (float) ($this->burnRate->lastMonths($user, 3)['burn_rate'] ?? 0);
            if ($burn > 0) {
                $coverage = (float) $fund->current_amount / $burn;

                $coverageThresholds = [
                    1 => ['tier' => 'small', 'months' => '1 mês'],
                    3 => ['tier' => 'medium', 'months' => '3 meses'],
                    6 => ['tier' => 'large', 'months' => '6 meses'],
                ];

                foreach ($coverageThresholds as $months => $meta) {
                    $dedup = "financial_health_emergency_{$months}m";
                    if ($coverage >= $months && !in_array($dedup, $existing, true)) {
                        $milestones[] = new MilestoneDTO(
                            type: $dedup,
                            tier: $meta['tier'],
                            title: "Reserva de emergência: {$meta['months']}",
                            body: "Sua reserva agora cobre {$meta['months']} de gastos. Tranquilidade real.",
                            dedupKey: $dedup,
                        );
                    }
                }
            }
        }

        // Savings rate milestones (from latest snapshot)
        $latestSnapshot = NetWorthSnapshot::where('user_id', $user->id)
            ->orderByDesc('captured_on')
            ->first();

        if ($latestSnapshot) {
            $rate = (float) $latestSnapshot->savings_rate;
            $rateThresholds = [
                20 => ['tier' => 'small'],
                30 => ['tier' => 'medium'],
                50 => ['tier' => 'large'],
            ];

            foreach ($rateThresholds as $threshold => $meta) {
                $dedup = "financial_health_savings_rate_{$threshold}";
                if ($rate >= $threshold && !in_array($dedup, $existing, true)) {
                    $milestones[] = new MilestoneDTO(
                        type: $dedup,
                        tier: $meta['tier'],
                        title: "Poupança >{$threshold}% no mês",
                        body: "Mês fechado com taxa de poupança de " . number_format($rate, 1, ',', '.') . '%.',
                        dedupKey: $dedup,
                    );
                }
            }
        }

        return $milestones;
    }
}
