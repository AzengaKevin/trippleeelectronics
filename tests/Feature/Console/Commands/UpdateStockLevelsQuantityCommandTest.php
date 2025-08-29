<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateStockLevelsQuantityCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_app_update_stock_levels_quantity_command()
    {

        $store = Store::factory()->create();

        $item = Item::factory()->create();

        $itemStockMovement = StockMovement::factory()->for($item, 'stockable')->for($store)->create(['quantity' => 10]);

        $item2 = Item::factory()->create();

        $variant = ItemVariant::factory()->for(Item::factory(), 'item')->create();

        $variant2 = ItemVariant::factory()->for(Item::factory(), 'item')->create();

        $variant2StockMovement = StockMovement::factory()->for($variant2, 'stockable')->for($store)->create(['quantity' => 5]);

        $this->artisan('app:update-stock-levels-quantity')
            ->expectsOutput('Updating stock levels quantity...')
            ->expectsOutput('Updated stock levels quantities successfully.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('stock_levels', [
            'store_id' => $store->id,
            'stockable_type' => $item->getMorphClass(),
            'stockable_id' => $item->id,
            'quantity' => $itemStockMovement->quantity,
        ]);

        $this->assertDatabaseHas('stock_levels', [
            'store_id' => $store->id,
            'stockable_type' => $variant2->getMorphClass(),
            'stockable_id' => $variant2->id,
            'quantity' => $variant2StockMovement->quantity,
        ]);

        $this->assertDatabaseMissing('stock_levels', [
            'store_id' => $store->id,
            'stockable_type' => $item2->getMorphClass(),
            'stockable_id' => $item2->id,
        ]);

        $this->assertDatabaseMissing('stock_levels', [
            'store_id' => $store->id,
            'stockable_type' => $variant->getMorphClass(),
            'stockable_id' => $variant->id,
        ]);
    }
}
