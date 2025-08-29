<?php

namespace App\Http\Requests;

use App\Models\Enums\ClientType;
use App\Models\PaymentMethod;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class POSOrderRequest extends FormRequest
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
            'items.*.product' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.taxable' => ['nullable', 'boolean'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_rate' => ['nullable', 'numeric', 'min:0'],
            'customer' => ['nullable', 'required_if:pay_later,true', 'array'],
            'customer.id' => ['nullable', 'required_if:pay_later,true', 'string'],
            'customer.type' => ['nullable', 'string', Rule::in(ClientType::options())],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'required_if:pay_later,true', 'string', 'max:20'],
            'customer.address' => ['nullable', 'string', 'max:255'],
            'customer.kra_pin' => ['nullable', 'string', 'max:20'],
            'customer.id_number' => ['nullable', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:0'],
            'pay_later' => ['nullable', 'boolean'],
            'payments' => ['required_unless:pay_later,true', 'array'],
            'payments.*.payment_method' => ['required', 'uuid', Rule::exists(PaymentMethod::class, 'id')],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.phone_number' => ['nullable', 'string', 'max:20'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'tendered_amount' => ['required_if:payment_method,cash'],
            'balance_amount' => ['required_if:payment_method,cash'],
        ];
    }

    public function messages(): array
    {
        return [
            'store.required' => 'The store is required.',
            'items.required' => 'At least one item is required.',
            'customer.required_if' => 'Customer information is required when paying later.',
            'customer.id.required_if' => 'Customer is required when paying later.',
            'customer.phone.required_if' => 'Customer phone is required when paying later.',
            'customer.email.email' => 'The customer email must be a valid email address.',
            'customer.phone.string' => 'The customer phone must be a string.',
            'payments.*.payment_method.exists' => 'The selected payment method does not exist.',
            'payments.*.amount.min' => 'Payment amounts must be at least 0.',
            'total_amount.required' => 'The total amount is required.',
        ];
    }
}
