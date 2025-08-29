<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Item;
use App\Models\ItemVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateItemsSellingPriceFromPriceCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_update_items_selling_price_from_price_command()
    {
        $items = Item::factory(3)->create(['selling_price' => null]);

        $this->artisan('app:update-items-selling-price-from-price')
            ->expectsOutput('Updating items selling prices with factor: 1.1')
            ->expectsOutput('Items selling prices updated successfully.')
            ->assertExitCode(0);

        $items->each(function ($item) {

            $expectedSellingPrice = round($item->price * 1.1, 2);

            $this->assertEquals($expectedSellingPrice, $item->fresh()->selling_price, "Selling price for item ID {$item->id} was not updated correctly.");
        });
    }

    public function test_update_items_selling_price_from_price_command_custom_factor()
    {
        $items = Item::factory(3)->create(['selling_price' => null]);

        $this->artisan('app:update-items-selling-price-from-price', ['--factor' => 1.2])
            ->expectsOutput('Updating items selling prices with factor: 1.2')
            ->expectsOutput('Items selling prices updated successfully.')
            ->assertExitCode(0);

        $items->each(function ($item) {

            $expectedSellingPrice = round($item->price * 1.2, 2);

            $this->assertEquals($expectedSellingPrice, $item->fresh()->selling_price, "Selling price for item ID {$item->id} was not updated correctly.");
        });
    }

    public function test_update_items_selling_price_from_price_command_for_variants()
    {
        $variants = ItemVariant::factory(3)->for(Item::factory())->create(['selling_price' => null]);

        $this->artisan('app:update-items-selling-price-from-price')
            ->expectsOutput('Updating items selling prices with factor: 1.1')
            ->expectsOutput('Items selling prices updated successfully.')
            ->assertExitCode(0);

        $variants->each(function ($variant) {
            $expectedSellingPrice = round($variant->price * 1.1, 2);

            $this->assertEquals($expectedSellingPrice, $variant->fresh()->selling_price, "Selling price for variant ID {$variant->id} was not updated correctly.");
        });
    }
}
