<?php

namespace App\Models;

use App\Models\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class PaymentMethod extends Model
{
    use HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'properties',
        'required_fields',
        'default_payment_status',
        'phone_number',
        'paybill_number',
        'account_number',
        'till_number',
        'account_name',
    ];

    protected function casts()
    {
        return [
            'properties' => 'array',
            'required_fields' => 'array',
            'default_payment_status' => PaymentStatus::class,
        ];
    }

    public function toSearchableArray()
    {
        return $this->only([
            'name',
            'description',
            'properties',
        ]);
    }

    public static function getExportFilename(): string
    {
        return str('payment-methods')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->value();
    }
}
