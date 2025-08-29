<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum FulfillmentStatus: string
{
    use WithOptions;

    case UNFULFILLED = 'unfulfilled';
    case PARTIALLY_FULFILLED = 'partially_fulfilled';
    case FULFILLED = 'fulfilled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::UNFULFILLED => 'Unfulfilled',
            self::PARTIALLY_FULFILLED => 'Partially Fulfilled',
            self::FULFILLED => 'Fulfilled',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
        };
    }
}
