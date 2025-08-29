<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Brand;
use App\Models\ItemCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductCategoryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_products_categories_index_route_for_item_categories()
    {
        $category = ItemCategory::factory()->create();

        $response = $this->get(route('products.categories.index', [
            'slug' => $category->slug,
        ]));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->hasAll('products', 'category', 'settings', 'categories', 'treeCategories', 'services'));
    }

    public function test_products_categories_index_route_for_brands()
    {
        $brand = Brand::factory()->create();

        $response = $this->get(route('products.categories.index', [
            'slug' => $brand->slug,
        ]));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->hasAll('products', 'brand', 'settings', 'categories', 'treeCategories', 'services'));
    }
}
