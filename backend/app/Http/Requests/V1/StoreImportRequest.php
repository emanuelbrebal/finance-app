<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:ofx,qfx,csv,txt', 'max:10240'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'importer' => ['nullable', 'string', 'in:ofx,nubank_csv,nubank_card_csv,generic_csv'],
            'mapping' => ['nullable', 'array'],
            'mapping.date' => ['required_with:mapping', 'string'],
            'mapping.description' => ['required_with:mapping', 'string'],
            'mapping.amount' => ['required_with:mapping', 'string'],
            'mapping.direction' => ['nullable', 'string'],
        ];
    }
}
