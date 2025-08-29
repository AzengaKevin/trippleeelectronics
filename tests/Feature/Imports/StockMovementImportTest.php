<?php

namespace Tests\Feature\Imports;

use App\Imports\StockMovementImport;
use App\Models\Enums\StockMovementType;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class StockMovementImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_stock_movement_import(): void
    {

        $titlesRow = [
            'product_sku',
            'store_short_name',
            'store_name',
            'product_name',
            'type',
            'quantity',
        ];

        $data = collect(1, 2)->map(function () {

            $store = Store::factory()->create();

            $item = Item::factory()->create();

            return [
                'product_sku' => $item->sku,
                'store_short_name' => $store->short_name,
                'store_name' => $store->name,
                'product_name' => $item->name,
                'type' => $this->faker->randomElement(StockMovementType::options()),
                'quantity' => $this->faker->numberBetween(20, 50),
            ];
        })->all();

        $filename = 'initial-stock-movements'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $import = new StockMovementImport;

            $import->import($filePath);

            $this->assertCount(count($data), StockMovement::query()->get());
        } finally {

            $filePath = $this->deleteTestCsvFile($filename);

            $this->assertFileDoesNotExist($filePath);
        }
    }
}
