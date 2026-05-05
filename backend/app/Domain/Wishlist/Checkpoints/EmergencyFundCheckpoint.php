<?php

namespace App\Domain\Wishlist\Checkpoints;

use App\Domain\Calculators\BurnRateCalculator;
use App\Domain\Wishlist\Checkpoints\Contracts\CheckpointInterface;
use App\Domain\Wishlist\CheckpointResult;
use App\Models\Goal;
use App\Models\User;
use App\Models\WishlistItem;

class EmergencyFundCheckpoint implements CheckpointInterface
{
    private const MIN_COVERAGE_MONTHS = 6.0;

    public function __construct(private readonly BurnRateCalculator $burnRate) {}

    public function id(): string
    {
        return 'emergency_fund';
    }

    public function label(): string
    {
        return 'Reserva de emergência intacta';
    }

    public function evaluate(WishlistItem $item, User $user): CheckpointResult
    {
        $fund = Goal::where('user_id', $user->id)
            ->where('is_emergency_fund', true)
            ->first();

        if ($fund === null) {
            return CheckpointResult::passed(
                'Reserva não configurada — proteção desativada.',
            );
        }

        $burn = (float) ($this->burnRate->lastMonths($user, 3)['burn_rate'] ?? 0);

        if ($burn <= 0) {
            return CheckpointResult::passed('Sem burn rate calculável ainda.');
        }

        $remainingAfter = (float) $fund->current_amount - (float) $item->target_price;
        $coverage = $remainingAfter / $burn;

        if ($coverage >= self::MIN_COVERAGE_MONTHS) {
            return CheckpointResult::passed(
                'Sua reserva continua cobrindo ' . number_format($coverage, 1, ',', '.') . ' meses após a compra.',
            );
        }

        $pct = max(0, min(100, round(($coverage / self::MIN_COVERAGE_MONTHS) * 100, 1)));
        return CheckpointResult::failed(
            'A compra deixaria sua reserva com cobertura de ' . number_format($coverage, 1, ',', '.') . ' meses (mínimo: ' . self::MIN_COVERAGE_MONTHS . ').',
            $pct,
        );
    }
}
