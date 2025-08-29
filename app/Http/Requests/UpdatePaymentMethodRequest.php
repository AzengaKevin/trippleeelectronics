<?php

namespace App\Http\Requests;

use App\Models\Enums\PaymentStatus;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $method = request()->route('method');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(PaymentMethod::class)->ignore($method->id)],
            'description' => ['nullable', 'string', 'max:500'],
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
