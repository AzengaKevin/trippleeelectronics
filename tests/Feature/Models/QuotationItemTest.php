<?php

namespace Tests\Feature\Models;

use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuotationItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_quotation_item_record(): void
    {
        $quotation = Quotation::factory()->create();

        $item = Item::factory()->create();

        $attributes = [
            'quotation_id' => $quotation->id,
            'item_id' => $item->id,
            'item_type' => $item->getMorphClass(),
            'quantity' => $this->faker->randomDigitNotZero(),
            'price' => $this->faker->randomFloat(2, 1, 100),
        ];

        $quotationItem = QuotationItem::query()->create($attributes);

        $this->assertNotNull($quotationItem);

        $this->assertDatabaseHas('quotation_items', $attributes);
    }

    public function test_quotation_item_sub_total_calculation(): void
    {
        $quotationItem = QuotationItem::factory()->for(Quotation::factory())->for(Item::factory(), 'item')->create([
            'quantity' => 5,
            'price' => 20.00,
        ]);

        $this->assertEquals(100.00, $quotationItem->sub_total);
    }

    public function test_quotation_item_can_be_an_item_variant(): void
    {
        $quotation = Quotation::factory()->create();

        $variant = ItemVariant::factory()->for(Item::factory())->create();

        $quotationItem = QuotationItem::factory()->for($quotation)->for($variant, 'item')->create();

        $this->assertNotNull($quotationItem);

        $this->assertDatabaseHas('quotation_items', [
            'quotation_id' => $quotation->id,
            'item_id' => $variant->id,
            'item_type' => $variant->getMorphClass(),
        ]);
    }

    public function test_quotation_item_can_be_a_custom_item(): void
    {
        $quotation = Quotation::factory()->create();

        $customItem = CustomItem::factory()->create(['name' => 'Custom Item']);

        $quotationItem = QuotationItem::factory()->for($quotation)->for($customItem, 'item')->create();

        $this->assertNotNull($quotationItem);

        $this->assertDatabaseHas('quotation_items', [
            'quotation_id' => $quotation->id,
            'item_id' => $customItem->id,
            'item_type' => $customItem->getMorphClass(),
        ]);
    }

    public function test_quotation_item_belongs_to_quotation(): void
    {
        $quotation = Quotation::factory()->create();

        $quotationItem = QuotationItem::factory()->for($quotation)->for(Item::factory(), 'item')->create();

        $this->assertInstanceOf(Quotation::class, $quotationItem->quotation);

        $this->assertEquals($quotation->id, $quotationItem->quotation->id);
    }
}
