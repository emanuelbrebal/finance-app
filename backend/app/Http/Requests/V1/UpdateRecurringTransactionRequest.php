<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecurringTransactionRequest extends FormRequest
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
                'sometimes', 'integer',
                Rule::exists('accounts', 'id')->where('user_id', $userId),
            ],
            'category_id' => [
                'sometimes', 'nullable', 'integer',
                Rule::exists('categories', 'id')->where('user_id', $userId),
            ],
            'description' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'gt:0'],
            'direction' => ['sometimes', Rule::in(['in', 'out'])],
            'day_of_month' => ['sometimes', 'integer', 'between:1,31'],
            'starts_on' => ['sometimes', 'date'],
            'ends_on' => ['sometimes', 'nullable', 'date'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
