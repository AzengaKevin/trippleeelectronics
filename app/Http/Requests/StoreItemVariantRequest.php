<?php

namespace App\Http\Requests;

use App\Models\Item;
use App\Models\ItemVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreItemVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item' => ['required', Rule::exists(Item::class, 'id')],
            'attribute' => ['required', 'string'],
            'value' => ['required', 'string'],
            'name' => ['required', 'string', Rule::unique(ItemVariant::class)],
            'pos_name' => ['nullable', 'string', Rule::unique(ItemVariant::class)],
            'image' => ['nullable', File::image()->max(1024 * 1)],
            'images' => ['nullable', 'array'],
            'images.*' => [File::image()->max(1024 * 1)],
            'cost' => ['nullable', 'numeric'],
            'price' => ['nullable', 'numeric'],
            'selling_price' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'pos_description' => ['nullable', 'string'],
            'quantity' => ['nullable', 'integer'],
        ];
    }
}
