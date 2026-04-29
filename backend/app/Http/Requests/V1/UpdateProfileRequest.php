<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                    => ['sometimes', 'string', 'max:255'],
            'email'                   => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'password'                => ['nullable', 'confirmed', Password::min(8)],
            'target_net_worth'        => ['nullable', 'numeric', 'min:0'],
            'target_date'             => ['nullable', 'date', 'after:today'],
            'estimated_monthly_income'=> ['nullable', 'numeric', 'min:0'],
            'timezone'                => ['nullable', 'timezone'],
            'preferences'             => ['nullable', 'array'],
        ];
    }
}
