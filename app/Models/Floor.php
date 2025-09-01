<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'building_id',
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

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
}
