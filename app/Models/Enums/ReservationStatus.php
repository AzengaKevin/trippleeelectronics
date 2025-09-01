<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum ReservationStatus: string
{
    use WithOptions;

    case RESERVED = 'reserved';
    case IN_HOUSE = 'in-house';
    case CHECKED_OUT = 'checked-out';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no-show';

    public function label(): string
    {
        return match ($this) {
            self::RESERVED => 'Reserved',
            self::IN_HOUSE => 'In House',
            self::CHECKED_OUT => 'Checked Out',
            self::CANCELLED => 'Cancelled',
            self::NO_SHOW => 'No Show',
        };
    }
}
