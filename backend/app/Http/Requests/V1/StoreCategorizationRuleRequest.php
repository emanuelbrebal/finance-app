<?php

namespace App\Http\Requests\V1;

use App\Models\CategorizationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategorizationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'match_type' => ['required', Rule::in([
                CategorizationRule::MATCH_CONTAINS,
                CategorizationRule::MATCH_STARTS_WITH,
                CategorizationRule::MATCH_EXACT,
                CategorizationRule::MATCH_REGEX,
            ])],
            'pattern' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'priority' => ['nullable', 'integer', 'between:0,100'],
            'auto_learned' => ['nullable', 'boolean'],
        ];
    }
}
