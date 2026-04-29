<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishlistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'target_price' => ['required', 'numeric', 'gt:0'],
            'reference_url' => ['nullable', 'url', 'max:500'],
            'priority' => ['nullable', 'integer', 'between:1,5'],
            'quarantine_days' => ['nullable', 'integer', 'between:1,365'],
            'category_id' => [
                'nullable', 'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
        ];
    }
}
