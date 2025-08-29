<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Cash',
            'Mpesa',
            'Paybill',
            'Till',
            'Cheque',
            'Bank Transfer',
            'Credit Card',
            'Debit Card',
            'Other',
        ])->each(function ($method) {
            PaymentMethod::updateOrCreate(
                ['name' => $method],
                [
                    'description' => "Payment method: $method",
                    'properties' => [],
                ]
            );
        });
    }
}
