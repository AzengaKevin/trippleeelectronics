<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use App\Models\Enums\TransactionMethod;
use App\Models\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Transaction extends Model
{
    use HasAuthor, HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'payment_id',
        'transaction_method',
        'amount',
        'till',
        'paybill',
        'account_number',
        'phone_number',
        'reference',
        'local_reference',
        'status',
        'fee',
        'data',
    ];

    protected function casts()
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'transaction_method' => TransactionMethod::class,
            'data' => 'array',
            'status' => TransactionStatus::class,
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            $transaction->local_reference = self::generateTransactionReference();
        });
    }

    public static function generateTransactionReference()
    {

        do {

            $reference = str(str()->random(4))->append(substr((string) microtime(true) * 10000, -4))->append(str()->random(4))->value();
        } while (Transaction::query()->where('local_reference', $reference)->exists());

        return $reference;
    }

    public function toSearchableArray()
    {
        return $this->only([
            'transaction_method',
            'amount',
            'till',
            'paybill',
            'account_number',
            'phone_number',
            'reference',
            'local_reference',
        ]);
    }

    public static function getExportFilename(): string
    {
        return str('transactions-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}
