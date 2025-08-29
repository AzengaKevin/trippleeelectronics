<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum OrderStatus: string
{
    use WithOptions;

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::CANCELED => 'Canceled',
            self::REFUNDED => 'Refunded',
            self::FAILED => 'Failed',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::PENDING => 'badge-warning',
            self::PROCESSING => 'badge-info',
            self::COMPLETED => 'badge-success',
            self::CANCELED => 'badge-error',
            self::REFUNDED => 'badge-secondary',
            self::FAILED => 'badge-dark',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::PROCESSING => 'cyan',
            self::COMPLETED => 'green',
            self::CANCELED => 'red',
            self::REFUNDED => 'gray',
            self::FAILED => 'black'
        };
    }
}
