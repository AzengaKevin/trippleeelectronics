<?php

namespace App\Http\Requests;

use App\Models\Enums\TransactionMethod;
use App\Models\Enums\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'transaction_method' => ['required', 'string', Rule::in(TransactionMethod::options())],
            'till' => ['nullable', 'string', 'max:255'],
            'paybill' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(TransactionStatus::options())],
        ];
    }
}
