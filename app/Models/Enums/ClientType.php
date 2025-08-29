<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum ClientType: string
{
    use WithOptions;

    case INDIVIDUAL = 'individual';
    case ORGANIZATION = 'organization';

    public function label(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Individual',
            self::ORGANIZATION => 'Organization',
        };
    }
}
