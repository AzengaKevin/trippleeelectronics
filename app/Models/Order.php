<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\PaymentStatus;
use App\Models\Enums\RefundStatus;
use App\Models\Enums\ShippingStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Order extends Model implements HasMedia
{
    use HasAuthor, HasFactory, HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes;

    public const RECEIPT_MEDIA_COLLECTION = 'receipts';

    protected $fillable = [
        'user_id',
        'author_user_id',
        'store_id',
        'customer_id',
        'customer_type',
        'amount',
        'shipping_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'tendered_amount',
        'balance_amount',
        'order_status',
        'payment_status',
        'fulfillment_status',
        'shipping_status',
        'refund_status',
        'channel',
        'refferal_code',
        'notes',
    ];

    protected $appends = [
        'order_status_badge',
    ];

    protected function casts()
    {
        return [
            'amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'tendered_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'order_status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'fulfillment_status' => FulfillmentStatus::class,
            'shipping_status' => ShippingStatus::class,
            'refund_status' => RefundStatus::class,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {

            $order->reference = self::generateOrderReference($order->order_channel);
        });
    }

    public static function generateOrderReference($channel = null)
    {
        $now = now();

        $prefix = str($now->format('ym'))
            ->append($channel ? strtoupper($channel[0]) : 'X')
            ->append($now->format('d'))
            ->value();

        $lastOrder = Order::query()->withTrashed()->where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->reference, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $orderCount = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return $prefix.$orderCount;
    }

    public function orderStatusBadge(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {

            $orderStatus = OrderStatus::tryFrom(data_get($attributes, 'order_status'));

            return $orderStatus?->badge();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function completePayments(): MorphMany
    {
        return $this->payments()->status(PaymentStatus::PAID);
    }

    public function customer(): MorphTo
    {
        return $this->morphTo();
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function canMarkCompleted(): bool
    {
        return $this->order_status !== OrderStatus::COMPLETED;
    }

    public function isCompleted(): bool
    {
        return $this->order_status === OrderStatus::COMPLETED;
    }

    public function computedAmount()
    {
        return $this->items->sum(fn ($item) => $item->quantity * $item->price);
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

    public function isSimilar(Order $other): bool
    {

        $fields = ['customer_id', 'store_id', 'order_status', 'author_user_id', 'total_amount'];

        $sameFields = $this->only($fields) === $other->only($fields);

        $sameItems = $this->items->pluck('item_id')->all() == $other->items->pluck('item_id')->all();

        return $sameFields && $sameItems;
    }

    public function computedTotalAmount()
    {
        return $this->computedAmount() + $this->shipping_amount + $this->tax_amount - $this->discount_amount;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public static function getExportFilename(): string
    {
        return str('orders')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}
