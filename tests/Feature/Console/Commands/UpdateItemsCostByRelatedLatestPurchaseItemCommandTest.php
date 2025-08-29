<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateItemsCostByRelatedLatestPurchaseItemCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_update_items_cost_by_related_latest_purchase_item_command()
    {

        $item = Item::factory()->create();

        $variant = ItemVariant::factory()->for(Item::factory(), 'item')->create();

        $purchase = Purchase::factory()->for(Store::factory())->create();

        $purchaseItem = PurchaseItem::factory()->for($purchase)->for($item, 'item')->create([
            'cost' => 100.00,
        ]);

        $purchaseItemVariant = PurchaseItem::factory()->for($purchase)->for($variant, 'item')->create([
            'cost' => 150.00,
        ]);

        $this->artisan('app:update-items-cost-by-related-latest-purchase-item')
            ->expectsOutput('Updating items cost by related latest purchase item...')
            ->expectsOutput('Items cost updated successfully.')
            ->assertExitCode(0);

        $this->assertEquals($item->fresh()->cost, $purchaseItem->cost);

        $this->assertEquals($variant->fresh()->cost, $purchaseItemVariant->cost);
    }
}
