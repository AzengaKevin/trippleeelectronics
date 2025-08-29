<?php

namespace App\Http\Requests;

use App\Models\ItemCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreItemCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', Rule::exists(ItemCategory::class, 'id')],
            'name' => ['required', Rule::unique(ItemCategory::class, 'name')],
            'description' => ['nullable'],
            'image' => ['nullable', File::image()->max(1 * 1024)],
            'featured' => ['nullable', 'boolean'],
        ];
    }
}
