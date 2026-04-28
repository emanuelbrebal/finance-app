<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where('user_id', $userId)->whereNull('archived_at'),
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $userId)->whereNull('archived_at'),
            ],
            'occurred_on' => 'required|date_format:Y-m-d',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'direction' => 'required|string|in:in,out',
            'notes' => 'nullable|string|max:5000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'out_of_scope' => 'nullable|boolean',
        ];
    }
}
