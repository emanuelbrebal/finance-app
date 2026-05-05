<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'occurred_on' => $this->occurred_on->toDateString(),
            'description' => $this->description,
            'amount' => (string) $this->amount,
            'direction' => $this->direction,
            'notes' => $this->notes,
            'tags' => $this->tags ?? [],
            'out_of_scope' => $this->out_of_scope,
            'import_batch_id' => $this->import_batch_id,
            'recurring_transaction_id' => $this->recurring_transaction_id,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
