<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasAuthor, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'user_id',
        'reference',
        'customer_type',
        'customer_id',
        'store_id',
        'amount',
        'shipping_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
    ];

    protected function casts()
    {
        return [
            'amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($qoutation) {

            $qoutation->reference = self::generateQuotationReference();
        });
    }

    public static function generateQuotationReference()
    {
        $now = now();

        $prefix = str($now->format('ym'))->append($now->format('d'))->value();

        $lastQuotation = Quotation::where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->first();

        if ($lastQuotation) {
            $lastNumber = (int) substr($lastQuotation->reference, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $qoutationCount = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return $prefix.$qoutationCount;
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): MorphTo
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function totalDiscount(): float
    {
        return $this->items->sum(fn ($item) => $item->total_discount);
    }

    public function totalTax(): float
    {
        return $this->items->sum(fn ($item) => $item->total_tax);
    }

    public function totalPrice(): float
    {
        return $this->items->sum(fn ($item) => $item->total_price);
    }

    public function subTotal(): float
    {
        return $this->items->sum(fn ($item) => $item->sub_total);
    }

    public static function getExportFilename(): string
    {
        return str('quotations')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}
