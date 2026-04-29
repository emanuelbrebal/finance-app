<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:60',
                Rule::unique('categories', 'name')
                    ->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'kind' => 'required|string|in:income,expense',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'required|string|max:40',
            'is_essential' => 'sometimes|boolean',
            'monthly_budget' => 'sometimes|nullable|numeric|min:0',
        ];
    }
}
