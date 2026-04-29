<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date', 'after:today'],
            'account_id' => [
                'nullable', 'integer',
                Rule::exists('accounts', 'id')->where('user_id', $userId),
            ],
            'is_emergency_fund' => ['nullable', 'boolean'],
        ];
    }
}
