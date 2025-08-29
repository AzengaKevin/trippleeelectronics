<?php

namespace Tests\Feature\Models;

use App\Models\StockLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockLevelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_stock_level_record_can_be_created()
    {
        $store = \App\Models\Store::factory()->create();
        $item = \App\Models\Item::factory()->create();

        $attributes = [
            'store_id' => $store->id,
            'stockable_id' => $item->id,
            'stockable_type' => $item->getMorphClass(),
            'quantity' => $this->faker->numberBetween(1, 100),
        ];

        $stockLevel = StockLevel::query()->create($attributes);

        $this->assertNotNull($stockLevel);

        $this->assertNotNull($stockLevel->store);

        $this->assertNotNull($stockLevel->stockable);

        $this->assertDatabaseHas('stock_levels', $attributes);
    }

    public function test_creating_stock_level_record_from_factory(): void
    {

        $store = \App\Models\Store::factory()->create();
        $item = \App\Models\Item::factory()->create();

        $stockLevel = StockLevel::factory()->for($store)->for($item, 'stockable')->create();

        $this->assertNotNull($stockLevel);

    }
}
