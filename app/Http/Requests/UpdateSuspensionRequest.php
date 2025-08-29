<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSuspensionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee' => ['required', Rule::exists(Employee::class, 'id')],
            'from' => ['required', 'date'],
            'to' => ['nullable', 'date', 'after:from'],
            'reason' => ['required', 'string'],
        ];
    }
}
