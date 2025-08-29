<?php

namespace App\Http\Requests;

use App\Models\Enums\PaymentStatus;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(PaymentMethod::class)],
            'description' => ['nullable', 'string', 'max:400'],
            'required_fields' => ['nullable', 'array'],
            'default_payment_status' => ['nullable', Rule::in(PaymentStatus::options())],
            'phone_number' => ['nullable', 'max:25'],
            'paybill_number' => ['nullable', 'max:10'],
            'account_number' => ['nullable', 'max:10'],
            'till_number' => ['nullable', 'max:10'],
            'account_name' => ['nullable', 'max:255'],
        ];
    }
}
