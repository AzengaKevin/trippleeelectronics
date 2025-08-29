<?php

namespace App\Http\Requests;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brand = request()->route('brand');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Brand::class)->ignore($brand?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', File::image()->max(1 * 1024)],
        ];
    }
}
