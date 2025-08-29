<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateItemsQuantityCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_app_update_items_quantity_command(): void
    {
        $store = Store::factory()->create();

        $item = Item::factory()->create();

        $itemStockMovement = StockMovement::factory()->for($item, 'stockable')->for($store)->create(['quantity' => 10]);

        $item2 = Item::factory()->create();

        $variant = ItemVariant::factory()->for(Item::factory(), 'item')->create();

        $variant2 = ItemVariant::factory()->for(Item::factory(), 'item')->create();

        $variant2StockMovement = StockMovement::factory()->for($variant2, 'stockable')->for($store)->create(['quantity' => 5]);

        $this->artisan('app:update-items-quantity')
            ->expectsOutput('Starting to update items quantity...')
            ->expectsOutput('Items quantity updated successfully!')
            ->assertExitCode(0);

        $this->assertEquals($item->fresh()->quantity, $itemStockMovement->quantity);

        $this->assertEquals($item2->fresh()->quantity, 0);

        $this->assertEquals($variant->fresh()->quantity, 0);

        $this->assertEquals($variant2->fresh()->quantity, $variant2StockMovement->quantity);
    }
}
