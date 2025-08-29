<?php

namespace App\Http\Requests;

use App\Models\OrganizationCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', Rule::exists(OrganizationCategory::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:1024'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:organizations,email'],
            'phone' => ['nullable', 'string', 'max:255', 'unique:organizations,phone'],
            'kra_pin' => ['nullable', 'string', 'max:255', 'unique:organizations,kra_pin'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
