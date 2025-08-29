<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Tax extends Model
{
    use HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'jurisdiction_id',
        'name',
        'description',
        'rate',
        'type',
        'is_compound',
        'is_inclusive',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_compound' => 'boolean',
            'is_inclusive' => 'boolean',
        ];
    }

    public function jurisdiction(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class);
    }

    public function items(): MorphToMany
    {
        return $this->morphedByMany(Item::class, 'taxable')->using(Taxable::class)->withTimestamps();
    }

    public function variants(): MorphToMany
    {
        return $this->morphedByMany(ItemVariant::class, 'taxable')->using(Taxable::class)->withTimestamps();
    }

    public function toSearchableArray()
    {
        return $this->only(['name', 'rate', 'description', 'type']);
    }
}
