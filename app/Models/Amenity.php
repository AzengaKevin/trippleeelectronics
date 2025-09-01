<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Amenity extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['name', 'description'];

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'amenity_room')
            ->withPivot('description')
            ->using(AmenityRoom::class)
            ->withTimestamps();
    }
}
