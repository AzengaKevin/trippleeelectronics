<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', Rule::unique(Service::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', File::image()->max(1 * 1024)],
        ];
    }
}
