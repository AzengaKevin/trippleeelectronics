<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum StockMovementType: string
{
    use WithOptions;

    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
    case INITIAL = 'initial';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Purchase',
            self::SALE => 'Sale',
            self::TRANSFER => 'Transfer',
            self::ADJUSTMENT => 'Adjustment',
            self::INITIAL => 'Initial',
        };
    }

    public function manual(): bool
    {
        return match ($this) {
            self::ADJUSTMENT, self::INITIAL => true,
            default => false,
        };
    }

    public static function manualLabelledOptions(): array
    {
        return collect(self::cases())
            ->filter(fn ($case) => $case->manual())
            ->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])->all();
    }
}
