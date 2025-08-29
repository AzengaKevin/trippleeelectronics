<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SetProductsCostCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_set_products_cost_command()
    {
        $item = \App\Models\Item::factory()->create(['cost' => 0, 'price' => 100]);
        $variant = \App\Models\ItemVariant::factory()->create(['cost' => 0, 'price' => 200]);

        $this->artisan('app:set-products-cost')
            ->expectsOutput('Setting products cost...')
            ->expectsOutput('Products cost has been set successfully.')
            ->assertExitCode(0);

        $item->refresh();
        $variant->refresh();

        $this->assertEquals(80, $item->cost);
        $this->assertEquals(160, $variant->cost);
    }
}
