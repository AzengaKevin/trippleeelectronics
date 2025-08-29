<?php

namespace App\Http\Requests;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group' => ['nullable', 'string'],
            'site_name' => ['nullable', 'max:48', 'string'],
            'email' => ['nullable', 'max:96', 'email'],
            'phone' => ['nullable', 'max:20', 'string'],
            'kra_pin' => ['nullable', 'max:20', 'string'],
            'location' => ['nullable', 'max:120', 'string'],
            'show_categories_banner' => ['nullable', 'boolean'],
            'facebook_link' => ['nullable', 'url'],
            'tiktok_link' => ['nullable', 'url'],
            'instagram_link' => ['nullable', 'url'],
            'whatsapp_link' => ['nullable', 'url'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
            'show_receipt_footer' => ['nullable', 'boolean'],
            'show_receipt_header' => ['nullable', 'boolean'],
            'mpesa_payment_method' => ['nullable', Rule::exists(PaymentMethod::class, 'id')],
            'cash_payment_method' => ['nullable', Rule::exists(PaymentMethod::class, 'id')],
        ];
    }
}
