<?php

namespace App\Http\Requests;

use App\Models\Resource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Resource::class)],
            'route_name' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'count' => ['nullable', 'integer'],
            'required_permission' => ['nullable', 'string', 'max:255'],
            'morph_class' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', File::image()->max(1 * 1024)],
        ];
    }
}
