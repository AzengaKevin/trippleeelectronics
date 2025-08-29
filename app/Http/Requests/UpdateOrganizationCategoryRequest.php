<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = request()->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:organization_categories,name,'.$category->id,
            ],
        ];
    }
}
