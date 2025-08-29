<?php

namespace App\Http\Requests;

use App\Models\Enums\ClientType;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store' => ['required', Rule::exists(Store::class, 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.cost' => ['required', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'array'],
            'supplier.type' => ['nullable', 'string', Rule::in(ClientType::options())],
            'supplier.id' => ['nullable', 'string'],
            'supplier.email' => ['nullable', 'email', 'max:255'],
            'supplier.phone' => ['nullable', 'string', 'max:20'],
            'supplier.address' => ['nullable', 'string', 'max:255'],
            'supplier.kra_pin' => ['nullable', 'string', 'max:20'],
            'supplier.id_number' => ['nullable', 'string', 'max:20'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
