<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    public ?string $mpesa_payment_method = null;

    public ?string $cash_payment_method = null;

    public static function group(): string
    {
        return 'payment';
    }
}
