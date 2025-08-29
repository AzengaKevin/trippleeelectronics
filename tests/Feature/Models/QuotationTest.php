<?php

namespace Tests\Feature\Models;

use App\Models\CustomItem;
use App\Models\Individual;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Quotation;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_quotation_record(): void
    {
        $store = Store::factory()->create();

        $individual = Individual::factory()->create();

        $attributes = [
            'customer_type' => $individual->getMorphClass(),
            'customer_id' => $individual->id,
            'store_id' => $store->id,
            'amount' => $amount = $this->faker->randomFloat(2, 0, 1000),
            'shipping_amount' => null,
            'tax_amount' => null,
            'discount_amount' => null,
            'total_amount' => $amount,
            'notes' => $this->faker->text(100),
        ];

        $quotation = Quotation::query()->create($attributes);

        $this->assertNotNull($quotation);

        $this->assertDatabaseHas('quotations', $attributes);
    }

    public function test_quotation_belongs_to_store(): void
    {
        $store = Store::factory()->create();

        $quotation = Quotation::factory()->create(['store_id' => $store->id]);

        $this->assertTrue($quotation->store->is($store));
    }

    public function test_quotation_belongs_to_customer(): void
    {
        $individual = Individual::factory()->create();

        $quotation = Quotation::factory()->create(['customer_id' => $individual->id, 'customer_type' => $individual->getMorphClass()]);

        $this->assertTrue($quotation->customer->is($individual));
    }

    public function test_quotation_has_many_items(): void
    {
        $quotation = Quotation::factory()->create();

        $item = Item::factory()->create();

        $variant = ItemVariant::factory()->for(Item::factory(), 'item')->create();

        $customItem = CustomItem::factory()->create();

        $items = collect([$item, $variant, $customItem]);

        $items->each(function ($item) use ($quotation) {
            $quotation->items()->create([
                'item_id' => $item->id,
                'item_type' => $item->getMorphClass(),
                'quantity' => 1,
                'price' => 100.00,
            ]);
        });

        $this->assertCount($items->count(), $quotation->fresh()->items);
    }
}
