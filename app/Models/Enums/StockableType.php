<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum StockableType: string
{
    use WithOptions;

    case Item = 'item';
    case ItemVariant = 'item-variant';

    public function label(): string
    {
        return match ($this) {
            self::Item => 'Item',
            self::ItemVariant => 'Item Variant',
        };
    }
}
