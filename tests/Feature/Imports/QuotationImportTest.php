<?php

namespace Tests\Feature\Imports;

use App\Imports\QuotationImport;
use App\Models\Individual;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class QuotationImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_importing_quotation_logic()
    {

        $customer = Individual::factory()->create();

        $author = User::factory()->create();

        $store = Store::factory()->create();

        $store->users()->attach($author);

        $titlesRow = [
            'customer_phone',
            'author_phone',
            'store_short_name',
            'amount',
            'shipping_amount',
            'total_amount',
            'tax_amount',
            'discount_amount',
        ];

        $data = [
            [
                'customer_phone' => $customer->phone,
                'author_phone' => $author->phone,
                'store_short_name' => $store->short_name,
                'amount' => $amount = $this->faker->randomFloat(2, 1, 1000),
                'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 0, 100),
                'total_amount' => $amount + $shippingAmount,
                'tax_amount' => $taxAmount = $this->faker->randomFloat(2, 0, 100),
                'discount_amount' => $discountAmount = $this->faker->randomFloat(2, 0, 100),
            ],
            [
                'customer_phone' => $customer->phone,
                'author_phone' => $author->phone,
                'store_short_name' => $store->short_name,
                'amount' => $amount + 1000, // Different amount to test uniqueness
                'shipping_amount' => $shippingAmount + 100, // Different shipping amount
                'total_amount' => ($amount + 1000) + ($shippingAmount + 100),
                'tax_amount' => $taxAmount + 50, // Different tax amount
                'discount_amount' => $discountAmount + 20, // Different discount amount
            ],
        ];

        $filename = str('quotations')->append('-')->append(now()->format('Y-m-d'))->append('.csv')->value();

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $quotationImport = new QuotationImport;

            $quotationImport->import($filePath);
        } finally {

            collect($data)->each(function ($item) use ($customer, $author, $store) {
                $this->assertDatabaseHas('quotations', [
                    'customer_id' => $customer->id,
                    'author_user_id' => $author->id,
                    'store_id' => $store->id,
                    'amount' => data_get($item, 'amount'),
                    'shipping_amount' => data_get($item, 'shipping_amount'),
                    'total_amount' => data_get($item, 'total_amount'),
                    'tax_amount' => data_get($item, 'tax_amount'),
                    'discount_amount' => data_get($item, 'discount_amount'),
                ]);
            });

            unlink($filePath);
        }
    }
}
