<?php

namespace Tests\Feature\Imports;

use App\Imports\StockLevelImport;
use App\Models\Item;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class StockLevelImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_stock_level_import()
    {

        $store = Store::factory()->create();

        $item = Item::factory()->create();

        $item2 = Item::factory()->create();

        $titlesRow = [
            'store_short_name',
            'sku',
            'quantity',
        ];

        $data = [
            [
                'store_short_name' => $store->short_name,
                'sku' => $item->sku,
                'quantity' => 10,
            ],
            [
                'store_short_name' => $store->short_name,
                'sku' => $item2->sku,
                'quantity' => 20,
            ],
        ];

        $filename = str('stock-levels-')->append(now()->format('Y-m-d-H'))->append('.csv')->value();

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        try {

            $import = new StockLevelImport;

            $import->import($filePath);
        } finally {

            collect($data)->each(function ($row) use ($store) {

                $item = Item::where('sku', $row['sku'])->first();

                $this->assertDatabaseHas('stock_levels', [
                    'store_id' => $store->id,
                    'stockable_id' => $item->id,
                    'quantity' => $row['quantity'],
                ]);
            });

            unlink($filePath);
        }
    }
}
