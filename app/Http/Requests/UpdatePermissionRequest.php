<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permission = request()->route('permission');

        return [
            'name' => ['required', 'string', Rule::unique(Permission::class)->ignore($permission?->id)],
        ];
    }
}
