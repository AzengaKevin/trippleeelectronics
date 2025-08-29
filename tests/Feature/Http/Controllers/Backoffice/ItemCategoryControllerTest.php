<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\ItemCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ItemCategoryControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-item-categories',
            'create-item-categories',
            'update-item-categories',
            'import-item-categories',
            'export-item-categories',
            'delete-item-categories',
        ]);
    }

    public function test_backoffice_item_categories_index_route()
    {
        $response = $this->actingAs($this->admin)->get(route('backoffice.item-categories.index'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($assert) => $assert->has('categories.data'));
    }

    public function test_backoffice_item_categories_create_route(): void
    {
        $response = $this->actingAs($this->admin)->get(route('backoffice.item-categories.create'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->has('categories'));
    }

    public function test_backoffice_item_categories_store_route(): void
    {
        ItemCategory::factory()->count(2)->create();

        $payload = [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'featured' => $this->faker->boolean(),
        ];

        $response = $this->actingAs($this->admin)->post(route('backoffice.item-categories.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('item_categories', [
            ...$payload,
            'author_user_id' => $this->admin->id,
        ]);
    }

    public function test_backoffice_item_categories_store_route_with_category(): void
    {
        $category = ItemCategory::factory()->create();

        $payload = [
            'category' => $category->id,
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->admin)->post(route('backoffice.item-categories.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('item_categories', [
            'parent_id' => $category->id,
            'name' => $payload['name'],
            'description' => $payload['description'],
        ]);
    }

    public function test_backoffice_item_categories_store_route_with_image(): void
    {
        Storage::fake('public');

        $payload = [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('category.jpg'),
        ];

        $response = $this->actingAs($this->admin)->post(route('backoffice.item-categories.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('item_categories', [
            'name' => $payload['name'],
            'description' => $payload['description'],
        ]);

        /** @var ItemCategory $category */
        $category = ItemCategory::query()->where('name', $payload['name'])->first();

        $this->assertCount(1, $category->getMedia());
    }

    public function test_backoffice_item_categories_show_route(): void
    {
        $category = ItemCategory::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.item-categories.show', compact('category')));

        $response->assertOk();

        $response->assertInertia(fn (AssertableInertia $assertableInertia) => $assertableInertia->has('category'));
    }

    public function test_backoffice_item_categories_edit_route(): void
    {
        $category = ItemCategory::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.item-categories.edit', compact('category')));

        $response->assertOk();

        $response->assertInertia(fn (AssertableInertia $assertableInertia) => $assertableInertia->hasAll(['category', 'categories']));
    }

    public function test_backoffice_item_categories_update_route(): void
    {

        $this->withoutExceptionHandling();

        $category = ItemCategory::factory()->create();

        $payload = [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->admin)->patch(route('backoffice.item-categories.update', compact('category')), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('item_categories', $payload);
    }

    public function test_backoffice_item_categories_update_route_with_image(): void
    {
        Storage::fake('public');

        $this->withoutExceptionHandling();

        $category = ItemCategory::factory()->create();

        $payload = [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'image' => UploadedFile::fake()->image('category.jpg'),
        ];

        $response = $this->actingAs($this->admin)->patch(route('backoffice.item-categories.update', compact('category')), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('item_categories', [
            'name' => data_get($payload, 'name'),
            'description' => data_get($payload, 'description'),
        ]);

        $this->assertCount(1, $category->getMedia());
    }

    public function test_backoffice_item_categories_destroy_route(): void
    {

        $this->withoutExceptionHandling();

        $category = ItemCategory::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('backoffice.item-categories.destroy', compact('category')));

        $response->assertRedirect();

        $this->assertSoftDeleted($category);
    }

    public function test_backoffice_item_categories_destroy_route_with_forever_parameter(): void
    {

        $this->withoutExceptionHandling();

        $category = ItemCategory::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('backoffice.item-categories.destroy', compact('category')), [
            'forever' => true,
        ]);

        $response->assertRedirect();

        $this->assertModelMissing($category);
    }

    public function test_backoffice_item_categories_export_route(): void
    {
        Excel::fake();

        ItemCategory::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.item-categories.export'));

        $response->assertOk();

        Excel::assertDownloaded(ItemCategory::getExportFilename());
    }

    public function test_backoffice_item_categories_import_route_get_method(): void
    {
        $response = $this->actingAs($this->admin)->get(route('backoffice.item-categories.import'));

        $response->assertOk();
    }

    public function test_backoffice_item_categories_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->admin)->post(route('backoffice.item-categories.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('users.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}
