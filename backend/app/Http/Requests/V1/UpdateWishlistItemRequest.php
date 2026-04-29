<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWishlistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:180'],
            'target_price' => ['sometimes', 'numeric', 'gt:0'],
            'current_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'reference_url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'priority' => ['sometimes', 'integer', 'between:1,5'],
            'quarantine_days' => ['sometimes', 'integer', 'between:1,365'],
            'category_id' => [
                'sometimes', 'nullable', 'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
        ];
    }
}
