<?php

namespace App\Models\Enums;

use App\Models\Concerns\WithOptions;

enum TransactionMethod: string
{
    use WithOptions;

    case CASH = 'cash';
    case MPESA = 'mpesa';
    case PAYBILL = 'paybill';
    case TILL = 'till';
    case CHEQUE = 'cheque';
    case BANK_TRANSFER = 'bank-transfer';
    case CREDIT_CARD = 'credit-card';
    case DEBIT_CARD = 'debit-card';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::MPESA => 'Mpesa',
            self::PAYBILL => 'Paybill',
            self::TILL => 'Till',
            self::CHEQUE => 'Cheque',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CREDIT_CARD => 'Credit Card',
            self::DEBIT_CARD => 'Debit Card',
            self::OTHER => 'Other',
        };
    }
}
