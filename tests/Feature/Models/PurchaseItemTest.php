<?php

namespace Tests\Feature\Models;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_purchase_item_item(): void
    {
        $purchase = Purchase::factory()->create();

        /** @var Item $item */
        $item = Item::factory()->create();

        $attibutes = [
            'purchase_id' => $purchase->id,
            'item_id' => $item->id,
            'item_type' => $item->getMorphClass(),
            'quantity' => $this->faker->randomDigitNotZero(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
        ];

        $purchaseItem = PurchaseItem::query()->create($attibutes);

        $this->assertNotNull($purchaseItem);

        $this->assertNotNull($purchaseItem->purchase);

        $this->assertNotNull($purchaseItem->item);

        $this->assertEquals($purchaseItem->id, $item->purchaseItems()->first()->id);

        $this->assertDatabaseHas('purchase_items', $attibutes);
    }

    public function test_creating_a_purchase_item_from_factory(): void
    {
        $item = Item::factory()->create();

        $purchase = Purchase::factory()->create();

        $purchaseItem = PurchaseItem::factory()->for($purchase)->for($item, 'item')->create();

        $this->assertNotNull($purchaseItem);

        $this->assertNotNull($purchaseItem->purchase);

        $this->assertNotNull($purchaseItem->item);

        $this->assertEquals($purchaseItem->id, $item->purchaseItems()->first()->id);

        $this->assertDatabaseHas('purchase_items', [
            'id' => $purchaseItem->id,
            'quantity' => $purchaseItem->quantity,
            'cost' => $purchaseItem->cost,
        ]);
    }
}
