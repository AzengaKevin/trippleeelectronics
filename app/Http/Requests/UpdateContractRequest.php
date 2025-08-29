<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\Enums\ContractStatus;
use App\Models\Enums\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee' => ['required', Rule::exists(Employee::class, 'id')],
            'contract_type' => ['required', Rule::in(ContractType::options())],
            'status' => ['required', Rule::in(ContractStatus::options())],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'salary' => ['required', 'numeric'],
            'responsibilities' => ['nullable', 'array'],
            'responsibilities.*' => ['required', 'string', 'max:256'],
        ];
    }
}
