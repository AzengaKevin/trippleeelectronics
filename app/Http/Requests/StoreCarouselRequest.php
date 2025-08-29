<?php

namespace App\Http\Requests;

use App\Models\Enums\OrientationOption;
use App\Models\Enums\PositionOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreCarouselRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orientation' => ['nullable', Rule::in(OrientationOption::options())],
            'position' => ['nullable', Rule::in(PositionOption::options())],
            'link' => ['nullable', 'url'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
            'image' => ['nullable', File::image()->max(1 * 1024)],
        ];
    }
}
