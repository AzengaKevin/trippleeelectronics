<?php

namespace Tests\Feature\Models;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemCategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_item_category(): void
    {
        $attributes = [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'featured' => $this->faker->boolean(),
        ];

        $itemCategory = ItemCategory::create($attributes);

        $this->assertNotNull($itemCategory);

        $this->assertDatabaseHas('item_categories', $attributes);

        $this->assertNotNull($itemCategory->slug);
    }

    public function test_careating_an_item_category_from_factory(): void
    {
        $itemCategory = ItemCategory::factory()->create();

        $this->assertNotNull($itemCategory);
    }

    public function test_item_category_items_relationship_method(): void
    {
        $itemCategory = ItemCategory::factory()->create();

        Item::factory()->count($itemsCount = 2)->for($itemCategory, 'category')->create();

        $this->assertNotNull($itemCategory->items);

        $this->assertCount($itemsCount, $itemCategory->items);
    }

    public function test_nested_item_categories(): void
    {

        $category = ItemCategory::create([
            'name' => 'Foo',
            'children' => [
                [
                    'name' => 'Bar',

                    'children' => [
                        ['name' => 'Baz'],
                    ],
                ],
            ],
        ]);

        $this->assertNotNull($category);

        $this->assertNotNull(ItemCategory::query()->where('name', 'Foo')->first());
        $this->assertNotNull(ItemCategory::query()->where('name', 'Bar')->first());
        $this->assertNotNull(ItemCategory::query()->where('name', 'Baz')->first());

        $this->assertEquals(3, ItemCategory::query()->count());
    }

    public function test_item_category_author_relationship_method(): void
    {
        $itemCategory = ItemCategory::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(User::class, $itemCategory->author);

        $this->assertEquals($itemCategory->author->id, $itemCategory->author_user_id);
    }
}
