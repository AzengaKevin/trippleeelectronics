<?php

namespace Tests\Feature\Imports;

use App\Imports\InitialStockMovementImport;
use App\Models\Enums\StockMovementType;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class InitialStockMovementImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_initial_stock_movement_import()
    {
        $store = Store::factory()->create(['name' => 'Test Store']);

        $items = Item::factory()->count($itemsCount = 2)->create();

        $titlesRow = [
            'product_sku',
            'store_short_name',
            'product_name',
            'store_name',
            'initial_stock',
            'cost',
            'retail_price',
            'advertised_price',
        ];

        $data = $items->map(function ($item) use ($store) {
            return [
                'product_sku' => $item->sku,
                'store_short_name' => $store->short_name,
                'product_name' => $item->name,
                'store_name' => $store->name,
                'initial_stock' => $this->faker->numberBetween(1, 100),
                'cost' => $this->faker->randomFloat(2, 1, 100),
                'retail_price' => $this->faker->randomFloat(2, 1, 100),
                'advertised_price' => $this->faker->randomFloat(2, 1, 100),
            ];
        })->all();

        $filename = 'initial-stock-movements'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $import = new InitialStockMovementImport;

            $import->import($filePath);

            $this->assertCount($itemsCount, StockMovement::type(StockMovementType::INITIAL)->get());

            foreach ($data as $item) {
                $this->assertDatabaseHas('stock_movements', [
                    'type' => StockMovementType::INITIAL->value,
                    'store_id' => $store->id,
                    'stockable_id' => $items->firstWhere('sku', $item['product_sku'])->id,
                    'quantity' => $item['initial_stock'],
                ]);
            }

            $this->assertEquals(0, Item::query()->whereNull('cost')->count());

            $this->assertEquals(0, Item::query()->whereNull('price')->count());

            $this->assertEquals(0, Item::query()->whereNull('selling_price')->count());
        } finally {

            $filePath = $this->deleteTestCsvFile($filename);

            $this->assertFileDoesNotExist($filePath);
        }
    }
}
