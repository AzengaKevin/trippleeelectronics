<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasAuthor, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'name',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(GroupUser::class)->withTimestamps();
    }
}
