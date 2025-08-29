<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Brand;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ItemControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-items',
            'create-items',
            'read-items',
            'update-items',
            'delete-items',
            'export-items',
            'import-items',
        ]);
    }

    public function test_backoffice_items_index_route(): void
    {
        Item::factory()->count($itemsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.items.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->component('backoffice/items/IndexPage')->hasAll([
                'items',
                'params',
                'categories',
                'brands',
            ])
        );
    }

    public function test_backoffice_items_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.items.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('categories')
                ->has('brands')
        );
    }

    public function test_backoffice_items_store_route(): void
    {

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.items.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.index'));

        $this->assertDatabaseHas('items', [
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'description' => $payload['description'],
        ]);
    }

    public function test_backoffice_items_store_route_with_pos_details(): void
    {
        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'pos_name' => $this->faker->unique()->word(),
            'pos_description' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.items.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.index'));

        $this->assertDatabaseHas('items', [
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'pos_name' => $payload['pos_name'],
            'pos_description' => $payload['pos_description'],
            'description' => $payload['description'],
        ]);
    }

    public function test_backoffice_items_store_route_with_selling_price(): void
    {

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'selling_price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.items.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.index'));

        $this->assertDatabaseHas('items', [
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'selling_price' => $payload['selling_price'],
            'description' => $payload['description'],
        ]);
    }

    public function test_backoffice_items_store_route_with_image(): void
    {
        Storage::fake('public');

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->name(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('item.jpg'),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.items.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.index'));

        $item = Item::where('name', $payload['name'])->first();

        $this->assertCount(1, $item->getMedia());

        $this->assertEquals($item->image_url, $item->getFirstMedia()->getFullUrl());
    }

    public function test_backoffice_items_store_route_with_image_and_images(): void
    {
        Storage::fake('public');

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->name(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('item.jpg'),
            'images' => [
                \Illuminate\Http\UploadedFile::fake()->image('item1.jpg'),
                \Illuminate\Http\UploadedFile::fake()->image('item2.jpg'),
            ],
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.items.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.index'));

        $item = Item::where('name', $payload['name'])->first();

        $this->assertEquals($item->image_url, $item->getFirstMedia()->getFullUrl());

        $this->assertCount(3, $item->getMedia());
    }

    public function test_backoffice_items_show_route(): void
    {
        $item = Item::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.items.show', $item));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('item')
                ->where('item.id', $item->id)
                ->where('item.name', $item->name)
                ->where('item.description', $item->description)
        );
    }

    public function test_backoffice_items_edit_route(): void
    {
        $item = Item::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.items.edit', $item));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('brands')
                ->has('categories')
                ->has('item')
                ->where('item.id', $item->id)
                ->where('item.name', $item->name)
                ->where('item.description', $item->description)
        );
    }

    public function test_backoffice_items_update_route(): void
    {
        $item = Item::factory()->for(Brand::factory())->for(ItemCategory::factory(), 'category')->create();

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.items.update', $item), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.show', $item));

        $this->assertDatabaseHas('items', [
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'description' => $payload['description'],
        ]);
    }

    public function test_backoffice_items_update_route_with_pos_details(): void
    {
        $item = Item::factory()->for(Brand::factory())->for(ItemCategory::factory(), 'category')->create();

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'pos_name' => $this->faker->unique()->word(),
            'pos_description' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.items.update', $item), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.show', $item));

        $this->assertDatabaseHas('items', [
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'pos_name' => $payload['pos_name'],
            'pos_description' => $payload['pos_description'],
            'description' => $payload['description'],
        ]);
    }

    public function test_backoffice_items_update_route_with_price(): void
    {
        $item = Item::factory()->for(Brand::factory())->for(ItemCategory::factory(), 'category')->create();

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'selling_price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.items.update', $item), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.show', $item));

        $this->assertDatabaseHas('items', [
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'selling_price' => $payload['selling_price'],
            'description' => $payload['description'],
        ]);
    }

    public function test_backoffice_items_update_route_with_image(): void
    {
        Storage::fake('public');

        $item = Item::factory()->for(Brand::factory())->for(ItemCategory::factory(), 'category')->create();

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('item.jpg'),
        ];

        $response = $this->actingAs($this->user)->patch(route('backoffice.items.update', $item), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.show', $item));

        $this->assertDatabaseHas('items', [
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'description' => $payload['description'],
        ]);

        $this->assertCount(1, $item->fresh()->getMedia());

        $this->assertEquals($item->fresh()->image_url, $item->getFirstMedia()->getFullUrl());
    }

    public function test_backoffice_items_update_route_with_image_and_images(): void
    {
        Storage::fake('public');

        $item = Item::factory()->for(Brand::factory())->for(ItemCategory::factory(), 'category')->create();

        $category = ItemCategory::factory()->create();

        $brand = Brand::factory()->create();

        $payload = [
            'category' => $category->id,
            'brand' => $brand->id,
            'name' => $this->faker->unique()->name(),
            'cost' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->sentence(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('item.jpg'),
            'images' => [
                \Illuminate\Http\UploadedFile::fake()->image('item1.jpg'),
                \Illuminate\Http\UploadedFile::fake()->image('item2.jpg'),
            ],
        ];

        $response = $this->actingAs($this->user)->patch(route('backoffice.items.update', $item), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.show', $item));

        $this->assertDatabaseHas('items', [
            'name' => $payload['name'],
            'cost' => $payload['cost'],
            'price' => $payload['price'],
            'description' => $payload['description'],
        ]);

        $this->assertEquals($item->fresh()->image_url, $item->getFirstMedia()->getFullUrl());

        $this->assertCount(3, $item->fresh()->getMedia());
    }

    public function test_backoffice_items_destroy_route(): void
    {
        $item = Item::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.items.destroy', $item));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.index'));

        $this->assertSoftDeleted('items', [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
        ]);
    }

    public function test_backoffice_items_destroy_route_forever(): void
    {
        $item = Item::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.items.destroy', [$item, 'forever' => true]));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.items.index'));

        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
        ]);
    }

    public function test_backoffice_items_export_route(): void
    {
        Excel::fake();

        Item::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.items.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Item::getExportFileName());
    }

    public function test_backoffice_items_import_route_get_method(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.items.import'));

        $response->assertOk();
    }

    public function test_backoffice_items_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.items.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('items.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}
