<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum EmployeeDocument: string
{
    use WithOptions;

    case ID = 'id';
    case GOOD_CONDUCT = 'good-conduct';
    case KRA_PIN = 'kra-pin';
    case RESUME = 'resume';
    case QUALIFICATION = 'qualification';

    public function label()
    {
        return match ($this) {
            self::ID => 'Identification Number',
            self::GOOD_CONDUCT => 'Good Conduct',
            self::KRA_PIN => 'Kra Pin',
            self::RESUME => 'Resume',
            self::QUALIFICATION => 'Qualification',
        };
    }
}
