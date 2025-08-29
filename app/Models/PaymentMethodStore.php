<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PaymentMethodStore extends Pivot
{
    use HasUuids;

    protected $table = 'payment_method_store';

    protected $fillable = [
        'store_id',
        'payment_method_id',
        'phone_number',
        'paybill_number',
        'account_number',
        'till_number',
        'account_name',
    ];
}
