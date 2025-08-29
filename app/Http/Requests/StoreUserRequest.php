<?php

namespace App\Http\Requests;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone' => [
                'nullable',
                'string',
                'lowercase',
                'max:255',
                Rule::unique(User::class),
            ],
            'roles' => ['nullable', 'array'],
            'roles.*' => [Rule::exists(Role::class, 'id')],
            'stores' => ['nullable', 'array'],
            'stores.*' => [Rule::exists(Store::class, 'id')],
        ];
    }
}
