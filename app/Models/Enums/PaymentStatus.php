<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum PaymentStatus: string
{
    use WithOptions;

    case UNPAID = 'unpaid';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Unpaid',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::PAID => 'Paid',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
        };
    }
}
