<?php

namespace App\Http\Requests;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client' => ['required', 'array'],
            'client.id' => ['required', 'string', 'max:255'],
            'client.email' => ['nullable', 'email', 'max:255'],
            'client.phone' => ['nullable', 'string', 'max:20'],
            'client.address' => ['nullable', 'string', 'max:255'],
            'client.id_number' => ['nullable', 'string', 'max:50'],
            'client.kra_pin' => ['nullable', 'string', 'max:50'],
            'deal' => ['nullable', 'uuid'],
            'store' => ['nullable', 'string', 'uuid', Rule::exists(Store::class, 'id')],
            'content' => ['required', 'string'],
            'since' => ['required', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:since'],
        ];
    }
}
