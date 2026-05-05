<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportBatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'account' => new AccountResource($this->whenLoaded('account')),
            'importer' => $this->importer,
            'original_filename' => $this->original_filename,
            'rows_total' => $this->rows_total,
            'rows_imported' => $this->rows_imported,
            'rows_duplicated' => $this->rows_duplicated,
            'status' => $this->status,
            'error_message' => $this->when($this->status === 'failed', $this->error_message),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
