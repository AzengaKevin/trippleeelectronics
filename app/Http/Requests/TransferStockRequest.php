<?php

namespace App\Http\Requests;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', Rule::exists(Store::class, 'id')],
            'to' => ['required', Rule::exists(Store::class, 'id')],
            'item' => ['required', 'uuid'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
