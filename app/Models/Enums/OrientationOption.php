<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum OrientationOption: string
{
    use WithOptions;

    case LANDSCAPE = 'landscape';
    case POTRAIT = 'potrait';

    public function label(): string
    {
        return match ($this) {
            self::POTRAIT => 'Potrait',
            self::LANDSCAPE => 'Landscape',
        };
    }
}
