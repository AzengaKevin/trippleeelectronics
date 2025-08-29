<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SetSellingPricesCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_set_selling_prices_command()
    {
        // Create some items and variants with prices
        $item = \App\Models\Item::factory()->create(['price' => 100]);

        $variant = \App\Models\ItemVariant::factory()->create(['price' => 50]);

        // Run the command
        $this->artisan('app:set-selling-prices')
            ->expectsOutput('Setting selling prices for products...')
            ->expectsOutput('Selling prices have been set successfully.')
            ->assertExitCode(0);

        // Assert that the selling prices were set correctly
        $this->assertEquals(120, $item->fresh()->selling_price);
        $this->assertEquals(60, $variant->fresh()->selling_price);
    }
}
