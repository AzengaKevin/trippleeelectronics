<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupUser extends Pivot
{
    use HasUuids;

    protected $table = 'group_user';

    protected $fillable = [
        'group_id',
        'user_id',
    ];
}
