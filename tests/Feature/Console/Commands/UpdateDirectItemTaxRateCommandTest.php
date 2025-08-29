<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Item;
use App\Services\TaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateDirectItemTaxRateCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_update_direct_item_tax_rate_command(): void
    {
        $primaryTax = app(TaxService::class)->fetchPrimaryTax();

        $items = Item::factory()->count(2)->create();

        $this->artisan('app:update-taxables');

        $this->assertFalse($items->every(fn ($item) => $item->tax_rate == $primaryTax->rate));

        $this->artisan('app:update-direct-item-tax-rate')->assertSuccessful();

        $this->assertTrue($items->every(fn ($item) => $item->fresh()->tax_rate == $primaryTax->rate));
    }
}
