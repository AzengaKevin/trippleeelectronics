<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum StockMovementActionType: string
{
    use WithOptions;

    case Order = 'order';
    case Purchase = 'purchase';

    public function label(): string
    {
        return match ($this) {
            self::Order => 'Order',
            self::Purchase => 'Purchase',
        };
    }
}
