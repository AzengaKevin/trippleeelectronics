<?php

namespace App\Http\Requests;

use App\Models\Enums\ClientType;
use App\Models\PaymentMethod;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
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
            'payments' => ['nullable', 'array'],
            'payments.*.payment_method' => ['required', 'uuid', Rule::exists(PaymentMethod::class, 'id')],
            'payments.*.amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.product.required' => 'Product is required.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.integer' => 'Quantity must be an integer.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.cost.required' => 'Cost is required.',
            'items.*.cost.numeric' => 'Cost must be a number.',
            'items.*.cost.min' => 'Cost must be at least 0.',
        ];
    }
}
