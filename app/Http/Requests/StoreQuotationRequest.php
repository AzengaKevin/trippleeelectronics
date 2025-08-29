<?php

namespace App\Http\Requests;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store' => ['nullable', Rule::exists(Store::class, 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.taxable' => ['nullable', 'boolean'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_rate' => ['nullable', 'numeric', 'min:0'],
            'customer' => ['nullable', 'array'],
            'customer.type' => ['nullable', 'string', 'in:individual,organization'],
            'customer.id' => ['nullable', 'string'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:20'],
            'customer.address' => ['nullable', 'string', 'max:255'],
            'customer.kra_pin' => ['nullable', 'string', 'max:20'],
            'customer.id_number' => ['nullable', 'string', 'max:20'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
