<?php

namespace Tests\Feature\Models;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Jurisdiction;
use App\Models\Tax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaxTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_tax(): void
    {
        $jurisdiction = Jurisdiction::factory()->create();

        $attributes = [
            'jurisdiction_id' => $jurisdiction->id,
            'name' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'rate' => $this->faker->randomFloat(2, 0, 100),
            'type' => $this->faker->word(),
            'is_compound' => $this->faker->boolean(),
            'is_inclusive' => $this->faker->boolean(),
        ];

        $tax = Tax::query()->create($attributes);

        $this->assertModelExists($tax);

        $this->assertDatabaseHas('taxes', $attributes);
    }

    public function test_creating_tax_from_factory(): void
    {
        $tax = Tax::factory()->create();

        $this->assertModelExists($tax);
    }

    public function test_creating_an_item_tax(): void
    {
        $item = Item::factory()->create();

        $tax = Tax::factory()->create();

        $item->taxes()->attach($tax);

        $this->assertDatabaseHas('taxables', [
            'taxable_id' => $item->id,
            'taxable_type' => $item->getMorphClass(),
            'tax_id' => $tax->id,
        ]);
    }

    public function test_creating_a_variant_tax(): void
    {
        $variant = ItemVariant::factory()->for(Item::factory())->create();

        $tax = Tax::factory()->create();

        $variant->taxes()->attach($tax);

        $this->assertDatabaseHas('taxables', [
            'taxable_id' => $variant->id,
            'taxable_type' => $variant->getMorphClass(),
            'tax_id' => $tax->id,
        ]);
    }
}
