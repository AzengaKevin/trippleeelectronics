<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum PaymentMethodFieldOption: string
{
    use WithOptions;

    case PHONE_NUMBER = 'phone_number';
    case PAYBILL_NUMBER = 'paybill_number';
    case ACCOUNT_NUMBER = 'account_number';
    case TILL_NUMBER = 'till_number';
    case ACCOUNT_NAME = 'account_name';

    public function label(): string
    {
        return match ($this) {
            self::PHONE_NUMBER => 'Phone Number',
            self::PAYBILL_NUMBER => 'Paybill Number',
            self::ACCOUNT_NUMBER => 'Account Number',
            self::TILL_NUMBER => 'Till Number',
            self::ACCOUNT_NAME => 'Account Name',
        };
    }
}
