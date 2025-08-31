<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum RoomStatus: string
{
    use WithOptions;

    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case MAINTENANCE = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::OCCUPIED => 'Occupied',
            self::MAINTENANCE => 'Under Maintenance',
        };
    }
}
