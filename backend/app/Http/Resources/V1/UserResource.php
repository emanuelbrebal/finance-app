<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'target_net_worth' => $this->target_net_worth,
            'target_date' => $this->target_date?->toDateString(),
            'estimated_monthly_income' => $this->estimated_monthly_income,
            'timezone' => $this->timezone,
            'journey_level' => $this->journey_level,
            'preferences' => $this->preferences ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
