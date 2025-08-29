<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MessageRead extends Pivot
{
    use HasUuids;

    protected $table = 'message_read';

    protected $fillable = [
        'message_id',
        'user_id',
    ];
}
