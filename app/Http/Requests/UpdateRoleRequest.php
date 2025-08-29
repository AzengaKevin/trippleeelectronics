<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = request()->route('role');

        return [
            'name' => ['required', 'string', Rule::unique(Role::class)->ignore($role?->id)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => Rule::exists(Permission::class, 'id'),
        ];
    }
}
