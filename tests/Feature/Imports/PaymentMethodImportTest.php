<?php

namespace Tests\Feature\Imports;

use App\Imports\PaymentMethodImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class PaymentMethodImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_payment_method_import()
    {
        $titlesRow = [
            'name',
            'description',
        ];

        $data = [
            [
                'name' => 'Credit Card',
                'description' => 'Payment via credit card',
            ],
            [
                'name' => 'PayPal',
                'description' => 'Payment via PayPal account',
            ],
        ];

        $filename = 'payment-methods'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {
            $import = new PaymentMethodImport;

            $import->import($filePath);

            foreach ($data as $item) {
                $this->assertDatabaseHas('payment_methods', [
                    'name' => $item['name'],
                    'description' => $item['description'],
                ]);
            }

        } finally {

            $filePath = $this->deleteTestCsvFile($filename);

            $this->assertFileDoesNotExist($filePath);
        }
    }
}
