<?php

namespace Tests\Feature\Imports;

use App\Imports\ItemImport;
use App\Models\Brand;
use App\Models\ItemCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class ItemImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_item_import_logic(): void
    {

        $brand = Brand::factory()->create();

        $category = ItemCategory::factory()->create();

        $titlesRow = [
            'name',
            'description',
            'category',
            'brand',
            'cost',
            'price',
            'selling_price',
        ];

        $data = [
            [
                'name' => $this->faker->sentence(),
                'description' => $this->faker->paragraph(),
                'category' => $category->name,
                'brand' => $brand->name,
                'cost' => $cost = $this->faker->randomFloat(2, 1, 100),
                'price' => $price = $this->faker->randomFloat(2, $cost, $cost + 50),
                'selling_price' => $this->faker->randomFloat(2, $price, $cost + 50),
            ],
            [
                'name' => $this->faker->sentence(),
                'description' => $this->faker->paragraph(),
                'category' => $category->name,
                'brand' => $brand->name,
                'cost' => $cost = $this->faker->randomFloat(2, 1, 100),
                'price' => $price = $this->faker->randomFloat(2, $cost, $cost + 50),
                'selling_price' => $this->faker->randomFloat(2, $price, $cost + 50),
            ],
            [
                'name' => $this->faker->sentence(),
                'description' => $this->faker->paragraph(),
                'category' => $category->name,
                'brand' => $brand->name,
                'cost' => $cost = $this->faker->randomFloat(2, 1, 100),
                'price' => $price = $this->faker->randomFloat(2, $cost, $cost + 50),
                'selling_price' => $this->faker->randomFloat(2, $price, $cost + 50),
            ],
        ];

        $filename = 'items'.now()->timestamp.'.csv';

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $import = new ItemImport;

            $import->import($filePath);

            foreach ($data as $row) {
                $this->assertDatabaseHas('items', [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'cost' => $row['cost'],
                    'selling_price' => $row['selling_price'],
                    'item_category_id' => $category->id,
                    'brand_id' => $brand->id,
                ]);
            }
        } finally {

            $this->deleteTestCsvFile($filename);
        }
    }
}
