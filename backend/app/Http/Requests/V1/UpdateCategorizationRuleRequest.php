<?php

namespace App\Http\Requests\V1;

use App\Models\CategorizationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategorizationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'match_type' => ['sometimes', Rule::in([
                CategorizationRule::MATCH_CONTAINS,
                CategorizationRule::MATCH_STARTS_WITH,
                CategorizationRule::MATCH_EXACT,
                CategorizationRule::MATCH_REGEX,
            ])],
            'pattern' => ['sometimes', 'string', 'max:255'],
            'category_id' => [
                'sometimes',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'priority' => ['sometimes', 'integer', 'between:0,100'],
        ];
    }
}
