<?php

namespace App\Http\Requests;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method' => ['required', 'uuid', Rule::exists(PaymentMethod::class, 'id')],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.phone_number' => ['nullable', 'string', 'max:20'],
        ];
    }
}
