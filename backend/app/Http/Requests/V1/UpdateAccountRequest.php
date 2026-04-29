<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|string|in:checking,savings,credit_card,cash,investment',
            'initial_balance' => 'sometimes|nullable|numeric|min:0',
            'color' => 'sometimes|nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'sometimes|nullable|string|max:40',
        ];
    }
}
