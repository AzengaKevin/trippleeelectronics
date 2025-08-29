<?php

namespace Tests\Feature\Imports;

use App\Imports\PurchaseImport;
use App\Models\Individual;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class PurchaseImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_purchase_import_logic(): void
    {
        $supplier = Individual::factory()->create();

        $author = User::factory()->create();

        $store = Store::factory()->create();

        $store->users()->attach($author);

        $titlesRow = [
            'author_phone',
            'supplier_phone',
            'store_short_name',
            'amount',
            'shipping_amount',
            'total_amount',
        ];

        $data = [
            [
                'author_phone' => $author->phone,
                'supplier_phone' => $supplier->phone,
                'store_short_name' => $store->short_name,
                'amount' => $amount = $this->faker->randomFloat(2, 1, 1000),
                'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 0, 100),
                'total_amount' => $amount + $shippingAmount,
            ],
            [
                'author_phone' => $author->phone,
                'supplier_phone' => $supplier->phone,
                'store_short_name' => $store->short_name,
                'amount' => $amount = $this->faker->randomFloat(2, 1, 1000),
                'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 0, 100),
                'total_amount' => $amount + $shippingAmount,
            ],
        ];

        $filename = 'purchases'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $import = new PurchaseImport;

            $import->import($filePath);

            collect($data)->each(function ($row) use ($author, $store, $supplier) {
                $this->assertDatabaseHas('purchases', [
                    'author_user_id' => $author->id,
                    'store_id' => $store->id,
                    'supplier_id' => $supplier->id,
                    'amount' => data_get($row, 'amount'),
                    'shipping_amount' => data_get($row, 'shipping_amount'),
                    'total_amount' => data_get($row, 'total_amount'),
                ]);
            });
        } finally {
            unlink($filePath);
        }
    }
}
