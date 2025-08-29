<?php

namespace App\Http\Requests;

use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\PaymentStatus;
use App\Models\Enums\RefundStatus;
use App\Models\Enums\ShippingStatus;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartiallyUpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author' => ['nullable', Rule::exists(User::class, 'id')],
            'store' => ['nullable', Rule::exists(Store::class, 'id')],
            'order_status' => ['nullable', Rule::in(OrderStatus::options())],
            'payment_status' => ['nullable', Rule::in(PaymentStatus::options())],
            'fulfillment_status' => ['nullable', Rule::in(FulfillmentStatus::options())],
            'shipping_status' => ['nullable', Rule::in(ShippingStatus::options())],
            'refund_status' => ['nullable', Rule::in(RefundStatus::options())],
            'channel' => ['nullable', 'max:255'],
            'notes' => ['nullable', 'max:65535'],
        ];
    }
}
