<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum ContractStatus: string
{
    use WithOptions;

    case ACTIVE = 'active';
    case EXPIRED = 'inactive';
    case TERMINATED = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::EXPIRED => 'Expired',
            self::TERMINATED => 'Terminated',
        };
    }
}
