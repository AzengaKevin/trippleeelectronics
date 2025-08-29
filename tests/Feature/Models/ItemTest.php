<?php

namespace Tests\Feature\Models;

use App\Models\Brand;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_item_with_only_the_required_fields(): void
    {

        $attributes = [
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
        ];

        $item = Item::create($attributes);

        $this->assertNotNull($item);

        $this->assertDatabaseHas('items', $attributes);
    }

    public function test_creating_a_new_item(): void
    {

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $attributes = [
            'item_category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'description' => $this->faker->sentence(),
        ];

        $item = Item::create($attributes);

        $this->assertNotNull($item);

        $this->assertDatabaseHas('items', $attributes);
    }

    public function test_creating_a_new_item_with_pos_name(): void
    {
        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $attributes = [
            'item_category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'pos_name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];

        $item = Item::query()->create($attributes);

        $this->assertNotNull($item);

        $this->assertDatabaseHas('items', $attributes);
    }

    public function test_creating_a_new_item_with_pos_description(): void
    {
        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $attributes = [
            'item_category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'pos_description' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
        ];

        $item = Item::query()->create($attributes);

        $this->assertNotNull($item);

        $this->assertDatabaseHas('items', $attributes);
    }

    public function test_creating_a_new_item_with_selling_price(): void
    {
        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $attributes = [
            'item_category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $price = $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'selling_price' => $price + $this->faker->randomFloat(2, 1, 100),
            'description' => $this->faker->sentence(),
        ];

        $item = Item::query()->create($attributes);

        $this->assertNotNull($item);

        $this->assertDatabaseHas('items', $attributes);
    }

    public function test_creating_a_new_item_with_image_url(): void
    {

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $attributes = [
            'item_category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'description' => $this->faker->sentence(),
            'image_url' => $this->faker->imageUrl(),
        ];

        $item = Item::query()->create($attributes);

        $this->assertNotNull($item);

        $this->assertDatabaseHas('items', $attributes);
    }

    public function test_creating_a_item_from_factory(): void
    {

        $item = Item::factory()->create();

        $this->assertNotNull($item);
    }

    public function test_item_category_relationship_method(): void
    {

        $category = ItemCategory::factory()->create();

        $item = Item::factory()->create(['item_category_id' => $category->id]);

        $this->assertInstanceOf(ItemCategory::class, $item->category);
    }

    public function test_item_brand_relationship_method(): void
    {

        $brand = Brand::factory()->create();

        $item = Item::factory()->create(['brand_id' => $brand->id]);

        $this->assertInstanceOf(Brand::class, $item->brand);
    }

    public function test_item_author_relationship_method(): void
    {

        $item = Item::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(User::class, $item->author);

        $this->assertEquals($item->author->id, $item->author_user_id);
    }
}
