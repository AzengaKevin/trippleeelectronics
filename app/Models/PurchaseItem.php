<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'item_type',
        'item_id',
        'quantity',
        'cost',
    ];

    protected function casts()
    {
        return [
            'quantity' => 'integer',
            'cost' => 'decimal:2',
        ];
    }

    protected $appends = [
        'sub_total',
    ];

    public function subTotal(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => intval(data_get($attributes, 'quantity')) * floatval(data_get($attributes, 'cost')));
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
