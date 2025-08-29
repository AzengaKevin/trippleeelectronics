<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemVariantTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_createing_a_new_item_variant_record(): void
    {

        $attributes = [
            'item_id' => \App\Models\Item::factory()->create()->id,
            'attribute' => $this->faker->word(),
            'value' => $this->faker->unique()->word(),
            'name' => $this->faker->unique()->word(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'description' => $this->faker->sentence,
            'quantity' => $this->faker->numberBetween(1, 100),
        ];

        $itemVariant = \App\Models\ItemVariant::create($attributes);

        $this->assertNotNull($itemVariant);

        $this->assertNotNull($itemVariant->sku);

        $this->assertNotNull($itemVariant->slug);

        $this->assertDatabaseHas('item_variants', $attributes);
    }

    public function test_creating_a_new_item_variant_with_pos_details(): void
    {
        $attributes = [
            'item_id' => \App\Models\Item::factory()->create()->id,
            'attribute' => $this->faker->word(),
            'value' => $this->faker->unique()->word(),
            'name' => $this->faker->unique()->word(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'description' => $this->faker->sentence,
            'quantity' => $this->faker->numberBetween(1, 100),
            'pos_name' => $this->faker->unique()->word(),
            'pos_description' => $this->faker->sentence,
        ];

        $itemVariant = \App\Models\ItemVariant::create($attributes);

        $this->assertNotNull($itemVariant);

        $this->assertNotNull($itemVariant->sku);

        $this->assertNotNull($itemVariant->slug);

        $this->assertDatabaseHas('item_variants', $attributes);
    }

    public function test_createing_a_new_item_variant_record_with_image_url(): void
    {

        $attributes = [
            'item_id' => \App\Models\Item::factory()->create()->id,
            'attribute' => $this->faker->word(),
            'value' => $this->faker->unique()->word(),
            'name' => $this->faker->unique()->word(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'description' => $this->faker->sentence,
            'quantity' => $this->faker->numberBetween(1, 100),
            'image_url' => $this->faker->imageUrl(),
        ];

        $itemVariant = \App\Models\ItemVariant::create($attributes);

        $this->assertNotNull($itemVariant);

        $this->assertNotNull($itemVariant->sku);

        $this->assertNotNull($itemVariant->slug);

        $this->assertDatabaseHas('item_variants', $attributes);
    }

    public function test_item_variant_item_relationship_method(): void
    {

        $item = \App\Models\Item::factory()->create();

        $itemVariant = \App\Models\ItemVariant::factory()->create(['item_id' => $item->id]);

        $this->assertInstanceOf(\App\Models\Item::class, $itemVariant->item);
    }

    public function test_item_variant_author_relationship_method(): void
    {

        $user = \App\Models\User::factory()->create();

        $itemVariant = \App\Models\ItemVariant::factory()->create(['author_user_id' => $user->id]);

        $this->assertInstanceOf(\App\Models\User::class, $itemVariant->author);
    }
}
