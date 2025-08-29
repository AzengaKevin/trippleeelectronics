<?php

namespace App\Http\Requests;

use App\Models\Enums\StockMovementActionType;
use App\Models\Enums\StockMovementType;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store' => ['required', Rule::exists(Store::class, 'id')],
            'product' => ['required', 'uuid'],
            'action_type' => ['nullable', Rule::in(StockMovementActionType::options())],
            'action_id' => ['nullable', 'uuid'],
            'type' => ['required', Rule::in(StockMovementType::options())],
            'quantity' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
            'cost_implication' => ['nullable', 'numeric'],
        ];
    }
}
