<?php

namespace App\Http\Requests;

use App\Models\Enums\StockableType;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store' => ['required', Rule::exists(Store::class, 'id')],
            'stockable_type' => [
                'required',
                Rule::in(StockableType::options()),
            ],
            'stockable_id' => [
                'required',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }
}
