<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum ShippingStatus: string
{
    use WithOptions;

    case UNSHIPPED = 'unshipped';
    case PARTIALLY_SHIPPED = 'partially_shipped';
    case SHIPPED = 'shipped';
    case RETURNED = 'returned';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::UNSHIPPED => 'Unshipped',
            self::PARTIALLY_SHIPPED => 'Partially Shipped',
            self::SHIPPED => 'Shipped',
            self::RETURNED => 'Returned',
            self::LOST => 'Lost',
        };
    }
}
