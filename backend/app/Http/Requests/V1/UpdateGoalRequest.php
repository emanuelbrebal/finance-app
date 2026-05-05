<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'target_amount' => ['sometimes', 'numeric', 'gt:0'],
            'current_amount' => ['sometimes', 'numeric', 'min:0'],
            'target_date' => ['sometimes', 'nullable', 'date'],
            'account_id' => [
                'sometimes', 'nullable', 'integer',
                Rule::exists('accounts', 'id')->where('user_id', $userId),
            ],
        ];
    }
}
