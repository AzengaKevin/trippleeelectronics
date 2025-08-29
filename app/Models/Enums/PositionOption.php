<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum PositionOption: string
{
    use WithOptions;

    case LEFT = 'left';
    case RIGHT = 'right';
    case CENTER = 'center';

    public function label(): string
    {
        return match ($this) {
            self::LEFT => 'Left',
            self::RIGHT => 'Right',
            self::CENTER => 'Center',
        };
    }
}
