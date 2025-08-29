<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum AccountType: string
{
    use WithOptions;

    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::ASSET => 'Asset',
            self::LIABILITY => 'Liability',
            self::EQUITY => 'Equity',
            self::REVENUE => 'Revenue',
            self::EXPENSE => 'Expense',
        };
    }

    public function multiplier(): int
    {
        return match ($this) {
            self::ASSET, self::REVENUE, self::EQUITY => 1,
            self::LIABILITY, self::EXPENSE => -1,
        };
    }
}
