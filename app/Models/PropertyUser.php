<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PropertyUser extends Pivot
{
    use HasUuids;

    protected $table = 'property_user';

    protected $fillable = [
        'property_id',
        'user_id',
    ];
}
