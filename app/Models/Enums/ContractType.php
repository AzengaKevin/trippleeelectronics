<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum ContractType: string
{
    use WithOptions;

    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case CONTRACT = 'contract';
    case TEMPORARY = 'temporary';
    case INTERN = 'intern';

    public function label(): string
    {
        return match ($this) {
            self::FULL_TIME => 'Full Time',
            self::PART_TIME => 'Part Time',
            self::CONTRACT => 'Contract',
            self::TEMPORARY => 'Temporary',
            self::INTERN => 'Intern',
        };
    }
}
