<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'target_amount' => (string) $this->target_amount,
            'current_amount' => (string) $this->current_amount,
            'progress_pct' => $this->progressPercent(),
            'target_date' => $this->target_date?->toDateString(),
            'account_id' => $this->account_id,
            'account' => new AccountResource($this->whenLoaded('account')),
            'is_emergency_fund' => $this->is_emergency_fund,
            'achieved_at' => $this->achieved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];

        // Monthly contribution needed to hit target_date
        if ($this->target_date && !$this->isAchieved()) {
            $remaining = max(0, (float) $this->target_amount - (float) $this->current_amount);
            $monthsLeft = max(1, now()->diffInMonths($this->target_date, false));
            $data['monthly_needed'] = number_format($remaining / $monthsLeft, 2, '.', '');
            $data['months_left'] = (int) $monthsLeft;
        }

        return $data;
    }
}
