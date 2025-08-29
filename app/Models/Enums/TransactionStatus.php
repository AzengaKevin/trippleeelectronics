<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum TransactionStatus: string
{
    use WithOptions;

    case PENDING = 'pending';
    case INITIATED = 'initiated';
    case COMPLETED = 'completed';
    case REVERSED = 'reversed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::INITIATED => 'Initiated',
            self::COMPLETED => 'Completed',
            self::REVERSED => 'Reversed',
        };
    }
}
