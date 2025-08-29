<?php

namespace App\Http\Requests;

use App\Models\PaymentMethod;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Store::class)],
            'short_name' => ['nullable', 'string', 'max:255', Rule::unique(Store::class)],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'paybill' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'till' => ['nullable', 'string', 'max:255'],
            'kra_pin' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*.id' => ['required', Rule::exists(PaymentMethod::class, 'id')],
            'payment_methods.*.phone_number' => ['nullable', 'string', 'max:255'],
            'payment_methods.*.paybill_number' => ['nullable', 'string', 'max:255'],
            'payment_methods.*.account_number' => ['nullable', 'string', 'max:255'],
            'payment_methods.*.till_number' => ['nullable', 'string', 'max:255'],
            'payment_methods.*.account_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
