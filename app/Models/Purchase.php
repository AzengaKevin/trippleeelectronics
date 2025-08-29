<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Purchase extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'store_id',
        'supplier_id',
        'supplier_type',
        'amount',
        'shipping_amount',
        'total_amount',
    ];

    protected function casts()
    {
        return [
            'amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {

            $purchase->reference = self::generatePurchaseReference();
        });
    }

    public static function generatePurchaseReference()
    {
        $now = now();

        $prefix = $now->format('ymd');

        $lastPurchase = \App\Models\Purchase::where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->first();

        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase->reference, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $purchaseCount = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return $prefix.$purchaseCount;
    }

    public function supplier(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public static function getExportFilename(): string
    {
        return str('purchases')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}
