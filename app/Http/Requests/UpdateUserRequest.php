<?php

namespace App\Http\Requests;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = request()->route('user');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user?->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'lowercase',
                'max:255',
                Rule::unique(User::class)->ignore($user?->id),
            ],
            'dob' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'roles' => ['nullable', 'array'],
            'roles.*' => [Rule::exists(Role::class, 'id')],
            'stores' => ['nullable', 'array'],
            'stores.*' => [Rule::exists(Store::class, 'id')],
        ];
    }
}
