<?php

namespace App\Models;

use App\Models\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Room extends Model
{
    use HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'property_id',
        'building_id',
        'floor_id',
        'room_type_id',
        'name',
        'code',
        'occupancy',
        'active',
        'status',
        'price',
    ];

    protected function casts()
    {
        return [
            'occupancy' => 'integer',
            'active' => 'boolean',
            'status' => RoomStatus::class,
            'price' => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room')
            ->withPivot('description')
            ->using(AmenityRoom::class)
            ->withTimestamps();
    }

    public function allocation(): HasOne
    {
        return $this->hasOne(Allocation::class);
    }

    public function toSearchableArray()
    {
        return $this->only(['name', 'code']);
    }

    public static function getExportFilename(): string
    {
        return str('rooms-')->append(now()->format('Y-m-d'))->append('.xlsx');
    }
}
