<?php

namespace App\Http\Requests;

use App\Models\Organization;
use App\Models\OrganizationCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $organization = request()->route('organization');

        return [
            'category' => ['nullable', Rule::exists(OrganizationCategory::class, 'id')],
            'name' => ['required', 'string', 'max:255', Rule::unique(Organization::class)->ignore($organization?->id)],
            'image' => ['nullable', 'image', 'max:1024'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(Organization::class)->ignore($organization?->id)],
            'phone' => ['nullable', 'string', 'max:255', Rule::unique(Organization::class)->ignore($organization?->id)],
            'kra_pin' => ['nullable', 'string', 'max:255', Rule::unique(Organization::class)->ignore($organization?->id)],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
