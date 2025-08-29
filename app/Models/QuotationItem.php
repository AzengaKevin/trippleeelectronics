<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'quotation_id',
        'item_id',
        'item_type',
        'quantity',
        'price',
        'taxable',
        'tax_rate',
        'discount_rate',
    ];

    protected $appends = [
        'sub_total',
        'total_discount',
        'total_tax',
        'total_price',
        'final_price_per_item',
    ];

    protected function casts()
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'taxable' => 'boolean',
            'tax_rate' => 'decimal:2',
            'discount_rate' => 'decimal:2',
        ];
    }

    public function totalDiscount(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => floatval(data_get($attributes, 'quantity')) * floatval(data_get($attributes, 'price')) * floatval(data_get($attributes, 'discount_rate')) / 100);
    }

    public function totalTax(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => floatval(data_get($attributes, 'quantity')) * floatval(data_get($attributes, 'price')) * floatval(data_get($attributes, 'tax_rate')) / 100);
    }

    public function totalPrice(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => floatval(data_get($attributes, 'quantity')) * floatval(data_get($attributes, 'price')) - $this->totalDiscount + $this->totalTax);
    }

    public function subTotal(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => intval(data_get($attributes, 'quantity')) * floatval(data_get($attributes, 'price')));
    }

    public function finalPricePerItem(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => floatval(data_get($attributes, 'price')) - (floatval(data_get($attributes, 'price')) * floatval(data_get($attributes, 'discount_rate')) / 100) + (floatval(data_get($attributes, 'price')) * floatval(data_get($attributes, 'tax_rate')) / 100));
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
