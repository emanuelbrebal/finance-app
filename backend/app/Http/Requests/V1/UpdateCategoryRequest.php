<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:60',
                Rule::unique('categories', 'name')
                    ->where(fn ($query) => $query->where('user_id', $this->user()->id))
                    ->ignore($categoryId),
            ],
            'kind' => 'sometimes|required|string|in:income,expense',
            'color' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'sometimes|required|string|max:40',
            'is_essential' => 'sometimes|boolean',
            'monthly_budget' => 'sometimes|nullable|numeric|min:0',
        ];
    }
}
