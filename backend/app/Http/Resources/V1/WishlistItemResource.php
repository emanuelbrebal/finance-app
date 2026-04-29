<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'target_price' => (string) $this->target_price,
            'current_price' => $this->current_price ? (string) $this->current_price : null,
            'reference_url' => $this->reference_url,
            'photo_path' => $this->photo_path,
            'priority' => $this->priority,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'quarantine_days' => $this->quarantine_days,
            'status' => $this->status,
            'days_in_wishlist' => (int) $this->created_at->diffInDays(now()),
            'purchased_transaction_id' => $this->purchased_transaction_id,
            'abandoned_at' => $this->abandoned_at?->toIso8601String(),
            'last_review_prompt_at' => $this->last_review_prompt_at?->toIso8601String(),
            'checkpoints' => $this->whenLoaded('checkpoints', $this->checkpoints ?? []),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
