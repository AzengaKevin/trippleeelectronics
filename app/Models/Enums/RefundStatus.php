<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum RefundStatus: string
{
    use WithOptions;

    case PENDING = 'pending';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
            self::FAILED => 'Failed',
        };
    }
}
