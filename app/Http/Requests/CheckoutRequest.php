<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:20'],
            'customer.address' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Items are required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.product.required' => 'Product ID is required.',
            'items.*.product.uuid' => 'Product ID must be a valid UUID.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.integer' => 'Quantity must be an integer.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.price.required' => 'Price is required.',
            'items.*.price.numeric' => 'Price must be a number.',
            'items.*.price.min' => 'Price must be at least 0.',
            'customer.required' => 'Customer information is required.',
            'customer.array' => 'Customer information must be an array.',
            'customer.name.required' => 'Customer name is required.',
            'customer.name.string' => 'Customer name must be a string.',
            'customer.name.max' => 'Customer name may not be greater than 255 characters.',
            'customer.email.required' => 'Customer email is required.',
            'customer.email.email' => 'Customer email must be a valid email address.',
            'customer.email.max' => 'Customer email may not be greater than 255 characters.',
            'customer.phone.required' => 'Customer phone number is required.',
            'customer.phone.string' => 'Customer phone number must be a string.',
            'customer.phone.max' => 'Customer phone number may not be greater than 20 characters.',
            'customer.address.required' => 'Customer address is required.',
            'customer.address.string' => 'Customer address must be a string.',
            'customer.address.max' => 'Customer address may not be greater than 255 characters.',
            'amount.required' => 'Total amount is required.',
            'amount.numeric' => 'Total amount must be a number.',
            'amount.min' => 'Total amount must be at least 0.',
        ];
    }
}
