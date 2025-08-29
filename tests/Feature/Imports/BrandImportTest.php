<?php

namespace Tests\Feature\Imports;

use App\Imports\BrandImport;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrandImportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_item_category_import()
    {
        $filepath = base_path('test-data/brands.xlsx');

        app(BrandImport::class)->import($filepath);

        $this->assertEquals(10, Brand::query()->count());
    }
}
