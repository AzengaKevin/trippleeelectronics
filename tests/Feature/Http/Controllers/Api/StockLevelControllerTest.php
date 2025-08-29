<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Enums\StockMovementType;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockLevelControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_api_stock_levels_index_route(): void
    {
        $items = Item::factory()->count($itemsCount = 2)->create();

        $stores = Store::factory()->count($storesCount = 2)->create();

        $items->crossJoin($stores)->each(function ($item) {

            [$item, $store] = $item;

            StockMovement::query()->create([
                'stockable_id' => $item->id,
                'stockable_type' => $item->getMorphClass(),
                'store_id' => $store->id,
                'quantity' => $this->faker->numberBetween(1, 100),
                'type' => StockMovementType::INITIAL,
            ]);
        });

        $response = $this->getJson(route('api.stock-levels.index', [
            'products' => $items->pluck('id')->toArray(),
        ]));

        $response->assertSuccessful();

        $response->assertJsonCount($itemsCount * $storesCount, 'data');
    }

    public function test_api_stock_levels_index_route_when_store_is_missing_stock(): void
    {
        $items = Item::factory()->count($itemsCount = 3)->create();

        $stores = Store::factory()->count($storesCount = 3)->create();

        $items->crossJoin($stores->take($storesCount - 1))->each(function ($item) {

            [$item, $store] = $item;

            StockMovement::query()->create([
                'stockable_id' => $item->id,
                'stockable_type' => $item->getMorphClass(),
                'store_id' => $store->id,
                'quantity' => $this->faker->numberBetween(1, 100),
                'type' => StockMovementType::INITIAL,
            ]);
        });

        $response = $this->getJson(route('api.stock-levels.index', [
            'products' => $items->pluck('id')->toArray(),
        ]));

        $response->assertSuccessful();

        $response->assertJsonCount($itemsCount * $storesCount, 'data');
    }

    public function test_api_stock_levels_index_route_only_for_one_store(): void
    {
        $items = Item::factory()->count($itemsCount = 3)->create();

        $stores = Store::factory()->count($storesCount = 3)->create();

        $store = $stores->first();

        $items->crossJoin($stores->take($storesCount - 1))->each(function ($item) {

            [$item, $store] = $item;

            StockMovement::query()->create([
                'stockable_id' => $item->id,
                'stockable_type' => $item->getMorphClass(),
                'store_id' => $store->id,
                'quantity' => $this->faker->numberBetween(1, 100),
                'type' => StockMovementType::INITIAL,
            ]);
        });

        $response = $this->getJson(route('api.stock-levels.index', [
            'products' => $items->pluck('id')->toArray(),
            'store' => $store->id,
        ]));

        $response->assertSuccessful();

        $response->assertJsonCount($itemsCount * 1, 'data');
    }
}
