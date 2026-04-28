<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'account_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $userId)->whereNull('archived_at'),
            ],
            'category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $userId)->whereNull('archived_at'),
            ],
            'occurred_on' => 'sometimes|required|date_format:Y-m-d',
            'description' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'direction' => 'sometimes|required|string|in:in,out',
            'notes' => 'sometimes|nullable|string|max:5000',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:50',
            'out_of_scope' => 'sometimes|nullable|boolean',
        ];
    }
}
