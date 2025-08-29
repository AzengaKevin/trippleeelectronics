<?php

namespace App\Http\Requests;

use App\Models\Thread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'thread_id' => ['required', Rule::exists(Thread::class, 'id')],
            'message' => ['required', 'string', 'max:400'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:2048'],
        ];
    }
}
