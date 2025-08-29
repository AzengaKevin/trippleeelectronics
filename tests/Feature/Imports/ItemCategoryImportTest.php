<?php

namespace Tests\Feature\Imports;

use App\Imports\ItemCategoryImport;
use App\Models\ItemCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemCategoryImportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_item_category_import()
    {
        $filepath = base_path('test-data/item-categories.xlsx');

        app(ItemCategoryImport::class)->import($filepath);

        $this->assertEquals(10, ItemCategory::query()->count());
    }
}
