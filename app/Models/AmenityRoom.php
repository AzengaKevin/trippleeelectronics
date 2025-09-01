<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AmenityRoom extends Pivot
{
    use HasUuids;

    protected $table = 'amenity_room';

    protected $fillable = [
        'amenity_id',
        'room_id',
        'description',
    ];
}
