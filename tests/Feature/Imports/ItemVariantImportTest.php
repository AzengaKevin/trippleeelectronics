<?php

namespace Tests\Feature\Imports;

use App\Imports\ItemVariantImport;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class ItemVariantImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_item_variant_import_logic(): void
    {

        $item = Item::factory()->create();

        $titlesRow = [
            'sku',
            'name',
            'attribute',
            'value',
            'cost',
            'price',
            'selling_price',
            'description',
        ];

        $data = [
            [
                'sku' => $item->sku,
                'name' => $this->faker->sentence(),
                'attribute' => $this->faker->unique()->word(),
                'value' => $this->faker->unique()->word(),
                'cost' => $cost = $this->faker->randomFloat(2, 1, 100),
                'price' => $price = $this->faker->randomFloat(2, $cost, $cost + 50),
                'selling_price' => $this->faker->randomFloat(2, $price, $cost + 50),
                'description' => $this->faker->sentence(),
            ],

        ];

        $filename = 'item_variants.csv';

        $filePath = $this->createTestCsvFile($filename, array_merge([$titlesRow], $data));

        // Import logic would go here
        $import = new ItemVariantImport;

        $import->import($filePath);

        // Clean up the test file
        $this->deleteTestCsvFile($filename);

        collect($data)->each(function ($row) {
            $this->assertDatabaseHas('item_variants', [
                'name' => $row['name'],
                'attribute' => $row['attribute'],
                'value' => $row['value'],
                'description' => $row['description'],
                'cost' => $row['cost'],
                'price' => $row['price'],
                'selling_price' => $row['selling_price'],
            ]);
        });
    }
}
