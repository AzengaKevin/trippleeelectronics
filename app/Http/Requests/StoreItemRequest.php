<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'uuid'],
            'brand' => ['nullable', 'uuid'],
            'name' => ['required', 'max:255', Rule::unique(Item::class)],
            'pos_name' => ['nullable', 'max:255', Rule::unique(Item::class)],
            'cost' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'selling_price' => ['nullable', 'numeric'],
            'description' => ['nullable'],
            'pos_description' => ['nullable'],
            'image' => ['nullable', File::image()->max(1024 * 1)],
            'images' => ['nullable', 'array'],
            'images.*' => [File::image()->max(1024 * 1)],
        ];
    }
}
