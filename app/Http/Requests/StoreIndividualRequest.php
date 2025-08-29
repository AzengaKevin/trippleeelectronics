<?php

namespace App\Http\Requests;

use App\Models\Individual;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreIndividualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization' => ['nullable', Rule::exists(Organization::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', File::image()->max(1 * 1024)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(Individual::class)],
            'phone' => ['nullable', 'string', 'max:255', Rule::unique(Individual::class)],
            'address' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique(Individual::class)],
            'kra_pin' => ['nullable', 'string', 'max:255', Rule::unique(Individual::class)],
            'id_number' => ['nullable', 'string', 'max:255', Rule::unique(Individual::class)],
        ];
    }
}
