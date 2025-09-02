<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Building extends Model
{
    use HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'property_id',
        'name',
        'code',
        'active',
    ];

    protected function casts()
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function toSearchableArray()
    {
        return $this->only(['name', 'code']);
    }

    public static function getExportFilename(): string
    {
        return str('buildings-')->append(now()->format('Y-m-d'))->append('.xlsx')->value();
    }
}
