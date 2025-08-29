<?php

namespace App\Http\Requests;

use App\Models\Enums\PaymentStatus;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payable' => ['nullable', 'string'],
            'payer' => ['nullable', 'string'],
            'payee' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', Rule::exists(PaymentMethod::class, 'id')],
            'status' => ['nullable', 'string', Rule::in(PaymentStatus::options())],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
