<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'overrides' => ['nullable', 'array'],
            'overrides.*.row_index' => ['required', 'integer', 'min:0'],
            'overrides.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
