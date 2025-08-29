<?php

namespace App\Http\Requests;

use App\Models\ItemCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateItemCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = request()->route('category');

        return [
            'category' => ['nullable', Rule::exists(ItemCategory::class, 'id')],
            'name' => ['required', Rule::unique(ItemCategory::class, 'name')->ignore($category?->id)],
            'image' => ['nullable', File::image()->max(1024)],
            'description' => ['nullable'],
            'featured' => ['nullable', 'boolean'],
        ];
    }
}
