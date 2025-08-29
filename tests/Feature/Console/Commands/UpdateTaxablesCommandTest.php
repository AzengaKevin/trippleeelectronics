<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Services\TaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTaxablesCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_update_taxables_command(): void
    {
        $items = Item::factory()->count(2)->create();

        $variants = ItemVariant::factory()->count(2)->create();

        $this->artisan('app:update-taxables')
            ->expectsOutput('Updating taxables for all items and item variants...')
            ->expectsOutput('Taxables updated successfully.')
            ->assertExitCode(0);

        /** @var \App\Models\Tax $primaryTax */
        $primaryTax = app(TaxService::class)->fetchPrimaryTax();

        $this->assertNotNull($primaryTax);

        $this->assertEquals($primaryTax->items->pluck('id')->all(), $items->pluck('id')->all());

        $this->assertEquals($primaryTax->variants->pluck('id')->all(), $variants->pluck('id')->all());
    }
}
