<?php

namespace Tests\Feature\Imports;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentImportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_payment_import_logic()
    {
        $path = base_path('test-data/payments.xlsx');

        $paymentImport = new \App\Imports\PaymentImport;

        $paymentImport->import($path);

        $this->assertDatabaseCount('payments', 5);
    }
}
