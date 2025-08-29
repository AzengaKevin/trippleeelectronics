<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jurisdiction' => ['required', Rule::exists('jurisdictions', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_compound' => ['required', 'boolean'],
            'is_inclusive' => ['required', 'boolean'],
        ];
    }
}
