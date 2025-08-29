<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CustomItem;
use App\Models\Enums\StockMovementType;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $stores;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stores = Store::factory()->count(3)->create();
    }

    public function test_api_products_index_route(): void
    {
        Item::factory()->count(2)->create();

        $response = $this->get(route('api.products.index'));

        $response->assertSuccessful();

        $response->assertJson(function (AssertableJson $assertableJson) {

            $assertableJson->has('data', 2, function (AssertableJson $productAssertableJson) {
                $productAssertableJson->hasAll(['id', 'sku', 'name', 'slug', 'cost', 'price', 'quantity'])->etc();
            })->etc();
        });
    }

    public function test_api_products_index_route_with_variants(): void
    {
        Item::factory()->count($itemsCount = 2)->create();

        $item = Item::factory()->create();

        ItemVariant::factory()->for($item)->count($variantsCount = 2)->create();

        $response = $this->get(route('api.products.index', [
            'includeVariants' => true,
        ]));

        $response->assertSuccessful();

        $response->assertJson(function (AssertableJson $assertableJson) use ($itemsCount, $variantsCount) {

            $assertableJson->has('data', $itemsCount + $variantsCount + 1, function (AssertableJson $productAssertableJson) {
                $productAssertableJson->hasAll(['id', 'sku', 'name', 'slug', 'cost', 'price', 'quantity'])->etc();
            })->etc();
        });
    }

    public function test_api_products_index_route_with_variants_and_custom_items(): void
    {
        Item::factory()->count($itemsCount = 2)->create();

        $item = Item::factory()->create();

        ItemVariant::factory()->for($item)->count($variantsCount = 2)->create();

        CustomItem::factory()->count($customItemsCount = 2)->create();

        $response = $this->get(route('api.products.index', [
            'includeVariants' => true,
            'includeCustomItems' => true,
        ]));

        $response->assertSuccessful();

        $response->assertJson(function (AssertableJson $assertableJson) use ($itemsCount, $variantsCount, $customItemsCount) {

            $assertableJson->has('data', $itemsCount + $variantsCount + $customItemsCount + 1, function (AssertableJson $productAssertableJson) {
                $productAssertableJson->hasAll(['id', 'sku', 'name', 'slug', 'cost', 'price', 'quantity'])->etc();
            })->etc();
        });
    }

    public function test_api_products_stock_levels_route(): void
    {
        $this->withoutExceptionHandling();

        $item = Item::factory()->create();

        $response = $this->get(route('api.products.stock-levels.index', ['product' => $item->id]));

        $response->assertSuccessful();

        $response->assertJson(function (AssertableJson $assertableJson) {
            $assertableJson->has('data', $this->stores->count(), function (AssertableJson $stockLevelsAssertableJson) {
                $stockLevelsAssertableJson->hasAll(['store', 'quantity'])->etc();
            })->etc();
        });
    }

    public function test_api_products_stock_levels_route_with_stock(): void
    {
        $this->withoutExceptionHandling();

        $item = Item::factory()->create();

        $this->stores->each(function ($store) use ($item) {
            StockMovement::factory()->for($store)->for($item, 'stockable')->create([
                'type' => StockMovementType::INITIAL,
                'quantity' => $this->faker->numberBetween(1, 100),
            ]);
        });

        $response = $this->get(route('api.products.stock-levels.index', [
            'product' => $item->id,
        ]));

        $response->assertSuccessful();

        $response->assertJson(function (AssertableJson $assertableJson) {
            $assertableJson->has('data', $this->stores->count(), function (AssertableJson $stockLevelsAssertableJson) {
                $stockLevelsAssertableJson->hasAll(['store', 'quantity'])->etc();
            })->etc();
        });
    }
}
