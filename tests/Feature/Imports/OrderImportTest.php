<?php

namespace Tests\Feature\Imports;

use App\Imports\OrderImport;
use App\Models\Enums\FulfillmentStatus;
use App\Models\Enums\OrderStatus;
use App\Models\Enums\RefundStatus;
use App\Models\Enums\ShippingStatus;
use App\Models\Individual;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class OrderImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_order_import_logic(): void
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
            'tendered_amount',
            'balance_amount',
            'order_status',
            'fulfillment_status',
            'shipping_status',
            'refund_status',
            'channel',
            'refferal_code',
            'notes',
        ];

        $data = [
            [
                'customer_phone' => $customer->phone,
                'author_phone' => $author->phone,
                'store_short_name' => $store->short_name,
                'amount' => $amount = $this->faker->randomFloat(2, 1, 1000),
                'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 0, 100),
                'total_amount' => $amount + $shippingAmount,
                'tax_amount' => $this->faker->randomFloat(2, 0, 50),
                'discount_amount' => $this->faker->randomFloat(2, 0, 50),
                'tendered_amount' => $this->faker->randomFloat(2, 0, 1000),
                'balance_amount' => $this->faker->randomFloat(2, 0, 1000),
                'order_status' => $this->faker->randomElement(OrderStatus::options()),
                'fulfillment_status' => $this->faker->randomElement(FulfillmentStatus::options()),
                'shipping_status' => $this->faker->randomElement(ShippingStatus::options()),
                'refund_status' => $this->faker->randomElement(RefundStatus::options()),
                'channel' => $this->faker->word(),
                'refferal_code' => $this->faker->word(),
                'notes' => $this->faker->sentence(),
            ],
            [
                'customer_phone' => $customer->phone,
                'author_phone' => $author->phone,
                'store_short_name' => $store->short_name,
                'amount' => $amount = $this->faker->randomFloat(2, 1, 1000),
                'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 0, 100),
                'total_amount' => $amount + $shippingAmount,
                'tax_amount' => $this->faker->randomFloat(2, 0, 50),
                'discount_amount' => $this->faker->randomFloat(2, 0, 50),
                'tendered_amount' => $this->faker->randomFloat(2, 0, 1000),
                'balance_amount' => $this->faker->randomFloat(2, 0, 1000),
                'order_status' => $this->faker->randomElement(OrderStatus::options()),
                'fulfillment_status' => $this->faker->randomElement(FulfillmentStatus::options()),
                'shipping_status' => $this->faker->randomElement(ShippingStatus::options()),
                'refund_status' => $this->faker->randomElement(RefundStatus::options()),
                'channel' => $this->faker->word(),
                'refferal_code' => $this->faker->word(),
                'notes' => $this->faker->sentence(),
            ],
        ];

        $filename = 'orders'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $import = new OrderImport;

            $import->import($filePath);

            foreach ($data as $row) {
                $this->assertDatabaseHas('orders', [
                    'author_user_id' => $author->id,
                    'customer_id' => $customer->id,
                    'store_id' => $store->id,
                    'amount' => data_get($row, 'amount'),
                    'shipping_amount' => data_get($row, 'shipping_amount'),
                    'total_amount' => data_get($row, 'total_amount'),
                    'tax_amount' => data_get($row, 'tax_amount'),
                    'discount_amount' => data_get($row, 'discount_amount'),
                    'tendered_amount' => data_get($row, 'tendered_amount'),
                    'balance_amount' => data_get($row, 'balance_amount'),
                    'order_status' => data_get($row, 'order_status'),
                    'fulfillment_status' => data_get($row, 'fulfillment_status'),
                    'shipping_status' => data_get($row, 'shipping_status'),
                    'refund_status' => data_get($row, 'refund_status'),
                    'channel' => data_get($row, 'channel'),
                    'refferal_code' => data_get($row, 'refferal_code'),
                    'notes' => data_get($row, 'notes'),
                ]);
            }
        } finally {
            unlink($filePath);
        }
    }
}
