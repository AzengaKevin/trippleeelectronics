<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $service = request()->route('service');

        return [
            'title' => ['required', 'string', 'max:255', Rule::unique(Service::class)->ignore($service?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', File::image()->max(1 * 1024)],
        ];
    }
}
