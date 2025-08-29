<?php

namespace Tests\Feature\Models;

use App\Models\Brand;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_brand(): void
    {
        $attributes = [
            'name' => $this->faker->unique()->name(),
            'description' => $this->faker->sentence(),
        ];

        $brand = Brand::query()->create($attributes);

        $this->assertNotNull($brand);

        $this->assertDatabaseHas('brands', $attributes);
    }

    public function test_creating_a_brand_from_a_factory(): void
    {

        $brand = Brand::factory()->create();

        $this->assertNotNull($brand);
    }

    public function test_brand_items_relationship_method(): void
    {
        $brand = Brand::factory()->create();

        Item::factory()->count($itemsCount = 2)->for($brand, 'brand')->create();

        $this->assertNotNull($brand->items);

        $this->assertCount($itemsCount, $brand->items);
    }

    public function test_brand_author_relationship_method(): void
    {
        $brand = Brand::factory()->for(User::factory(), 'author')->create();

        $this->assertNotNull($brand->author);

        $this->assertEquals($brand->author->id, $brand->author_user_id);
    }
}
