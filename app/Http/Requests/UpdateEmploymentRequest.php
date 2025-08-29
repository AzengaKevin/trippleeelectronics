<?php

namespace App\Http\Requests;

use App\Models\Enums\EmployeeDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateEmploymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = request()->user()->employee;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('employees')->ignore($employee->id)],
            'phone' => ['required', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'kra_pin' => ['nullable', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id)],
            'identification_number' => ['nullable', 'string', 'max:15', Rule::unique('employees')->ignore($employee->id)],
            'hire_date' => ['nullable', 'date'],
            'documents' => ['nullable', 'array', 'min:1'],
            'documents.*.type' => ['required', Rule::in(EmployeeDocument::options())],
            'documents.*.file' => ['required', File::types(['pdf', 'doc', 'docx', 'png', 'jpg'])],
        ];
    }
}
