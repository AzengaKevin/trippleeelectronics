<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'order_id',
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
        'final_price_per_item',
        'total_discount',
        'total_tax',
        'total_price',
    ];

    protected function casts()
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function totalDiscount(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {
            $price = floatval(data_get($attributes, 'price'));
            $discount = floatval(data_get($attributes, 'discount_rate')) / 100 * $price;
            $quantity = intval(data_get($attributes, 'quantity'));

            return $discount * $quantity;
        });
    }

    public function totalTax(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {
            $price = floatval(data_get($attributes, 'price'));
            $tax = floatval(data_get($attributes, 'tax_rate')) / 100 * $price;
            $quantity = intval(data_get($attributes, 'quantity'));

            return $tax * $quantity;
        });
    }

    public function totalPrice(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {
            $price = floatval(data_get($attributes, 'price'));
            $quantity = intval(data_get($attributes, 'quantity'));

            return $price * $quantity;
        });
    }

    public function finalPricePerItem(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {
            $price = floatval(data_get($attributes, 'price'));
            $tax = floatval(data_get($attributes, 'tax_rate')) / 100 * $price;
            $discount = floatval(data_get($attributes, 'discount_rate')) / 100 * $price;

            return $price + $tax - $discount;
        });
    }

    public function subTotal(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {

            $price = floatval(data_get($attributes, 'price'));
            $quantity = intval(data_get($attributes, 'quantity'));
            $tax = floatval(data_get($attributes, 'tax_rate')) / 100 * $price;
            $discount = floatval(data_get($attributes, 'discount_rate')) / 100 * $price;

            $subTotal = ($price + $tax - $discount) * $quantity;

            return $subTotal;
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
