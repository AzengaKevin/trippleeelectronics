<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $property = request()->route('property');

        return [
            'name' => ['required', Rule::unique(Property::class, 'name')->ignore($property->id)],
            'code' => ['nullable', Rule::unique(Property::class, 'code')->ignore($property->id)],
            'address' => ['nullable'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
