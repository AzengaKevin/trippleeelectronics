<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique(Property::class, 'name')],
            'code' => ['nullable', Rule::unique(Property::class, 'code')],
            'address' => ['nullable'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
