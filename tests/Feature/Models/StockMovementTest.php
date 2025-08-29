<?php

namespace Tests\Feature\Models;

use App\Models\Enums\StockMovementType;
use App\Models\Item;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_create_a_new_stock_movement_record(): void
    {
        $store = Store::factory()->create();

        $item = Item::factory()->create();

        $attributes = [
            'store_id' => $store->id,
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
            'type' => $this->faker->randomElement(StockMovementType::options()),
            'quantity' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->sentence(),
            'cost_implication' => $this->faker->randomFloat(2, 1, 1000),
        ];

        $stockMovement = StockMovement::query()->create($attributes);

        $this->assertNotNull($stockMovement);

        $this->assertDatabaseHas('stock_movements', $attributes);

        $this->assertEquals($stockMovement->quantity, $item->fresh()->quantity);

        $this->assertEquals(StockLevel::query()->where('store_id', $store->id)->first()->quantity, $stockMovement->quantity);
    }

    public function testing_a_new_stock_movement_with_a_related_one(): void
    {

        $store = Store::factory()->create();

        $item = Item::factory()->create();

        $relatedStockMovement = StockMovement::factory()->for($store)->for($item, 'stockable')->create([
            'type' => StockMovementType::PURCHASE,
            'quantity' => 10,
            'description' => 'Initial stock',
        ]);

        $attributes = [
            'store_id' => $store->id,
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
            'related_stock_movement_id' => $relatedStockMovement->id,
            'type' => StockMovementType::SALE,
            'quantity' => 5,
            'description' => 'Sold 5 items',
        ];

        $stockMovement = StockMovement::query()->create($attributes);

        $this->assertNotNull($stockMovement);

        $this->assertNotNull($stockMovement->relatedStockMovement);

        $this->assertDatabaseHas('stock_movements', $attributes);
    }

    public function test_stock_movement_robust_stockable_relationship(): void
    {
        $store = Store::factory()->create();

        $item = Item::factory()->create();

        StockMovement::factory()->for($store)->for($item, 'stockable')->create();

        $item->delete();

        $stockMovement = StockMovement::query()->first();

        $this->assertNull($stockMovement->stockable);

        $this->assertNotNull($stockMovement->robustStockable);

        $this->assertEquals($item->id, $stockMovement->robustStockable->id);
    }

    public function test_creating_stock_movement_with_author(): void
    {

        $store = Store::factory()->create();

        $item = Item::factory()->create();

        $author = User::factory()->create();

        $attributes = [
            'author_user_id' => $author->id,
            'store_id' => $store->id,
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
            'type' => $this->faker->randomElement(StockMovementType::options()),
            'quantity' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->sentence(),
            'cost_implication' => $this->faker->randomFloat(2, 1, 1000),
        ];

        $stockMovement = StockMovement::query()->create($attributes);

        $this->assertNotNull($stockMovement);

        $this->assertDatabaseHas('stock_movements', $attributes);

        $this->assertEquals($stockMovement->author?->id, $author->id);

        $this->assertEquals($stockMovement->quantity, $item->fresh()->quantity);

        $this->assertEquals(StockLevel::query()->where('store_id', $store->id)->first()->quantity, $stockMovement->quantity);
    }
}
