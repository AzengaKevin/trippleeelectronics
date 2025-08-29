<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_products_index_route()
    {

        Item::factory()->count($itemsCount = 2)->create();

        $response = $this->get(route('products.index'));

        $response->assertOk();

        $response->assertInertia(fn ($page) => $page->has('products.data', $itemsCount)
            ->has('categories')
            ->has('settings')
            ->has('treeCategories')
            ->has('services')
        );
    }

    public function test_products_show_route()
    {
        $item = Item::factory()->create();

        $response = $this->get(route('products.show', $item));

        $response->assertOk();

        $response->assertInertia(fn ($page) => $page->has('product')->has('categories')
            ->has('recommendedProducts')
            ->has('settings')
            ->has('treeCategories')
            ->has('services'));
    }
}
