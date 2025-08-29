<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use App\Models\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Payment extends Model
{
    use HasAuthor, HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'payable_id',
        'payable_type',
        'payer_id',
        'payer_type',
        'payee_id',
        'payee_type',
        'payment_method',
        'payment_method_id',
        'phone_number',
        'status',
        'amount',
    ];

    protected function casts()
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function payer(): MorphTo
    {
        return $this->morphTo();
    }

    public function payee(): MorphTo
    {
        return $this->morphTo();
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function scopeStatus($query, PaymentStatus $status)
    {
        return $query->where('status', $status);
    }

    public static function getExportFilename(): string
    {
        return str('payments-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }

    public function toSearchableArray()
    {
        return $this->only(['status', 'amount', 'payment_method']);
    }
}
