<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ThreadUser extends Pivot
{
    use HasUuids;

    protected $table = 'thread_user';

    protected $fillable = [
        'user_id',
        'thread_id',
    ];
}
