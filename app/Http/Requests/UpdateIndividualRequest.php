<?php

namespace App\Http\Requests;

use App\Models\Individual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateIndividualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $individual = request()->route('individual');

        return [
            'organization' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', File::image()->max(1 * 1024)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(Individual::class)->ignore($individual?->id)],
            'phone' => ['nullable', 'string', 'max:255', Rule::unique(Individual::class)->ignore($individual?->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique(Individual::class)->ignore($individual?->id)],
            'kra_pin' => ['nullable', 'string', 'max:255', Rule::unique(Individual::class)->ignore($individual?->id)],
            'id_number' => ['nullable', 'string', 'max:255', Rule::unique(Individual::class)->ignore($individual?->id)],
        ];
    }
}
