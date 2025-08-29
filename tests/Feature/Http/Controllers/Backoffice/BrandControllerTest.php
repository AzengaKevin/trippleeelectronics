<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-brands',
            'create-brands',
            'read-brands',
            'update-brands',
            'delete-brands',
            'export-brands',
            'import-brands',
        ]);
    }

    public function test_backoffice_brands_index_route(): void
    {
        Brand::factory()->count($brandsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.brands.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('brands.data', $brandsCount)
        );
    }

    public function test_backoffice_brands_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.brands.create'));

        $response->assertStatus(200);
    }

    public function test_backoffice_brands_store_route(): void
    {
        $payload = [
            'name' => $this->faker->unique()->name(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.brands.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.brands.index'));

        $this->assertDatabaseHas('brands', [
            ...$payload,
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_brands_store_route_with_image(): void
    {

        Storage::fake('public');

        $payload = [
            'name' => $this->faker->unique()->name(),
            'description' => $this->faker->sentence(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('brand.jpg'),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.brands.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.brands.index'));

        $brand = Brand::where('name', $payload['name'])->first();

        $this->assertNotNull($brand);

        $this->assertCount(1, $brand->getMedia());
    }

    public function test_backoffice_brands_show_route(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.brands.show', $brand));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('brand')
                ->where('brand.id', $brand->id)
                ->where('brand.name', $brand->name)
                ->where('brand.description', $brand->description)
        );
    }

    public function test_backoffice_brands_edit_route(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.brands.edit', $brand));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('brand')
                ->where('brand.id', $brand->id)
                ->where('brand.name', $brand->name)
                ->where('brand.description', $brand->description)
        );
    }

    public function test_backoffice_brands_update_route(): void
    {
        $brand = Brand::factory()->create();

        $payload = [
            'name' => $this->faker->unique()->name(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.brands.update', $brand), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.brands.show', $brand));

        $this->assertDatabaseHas('brands', $payload);
    }

    public function test_backoffice_brands_update_route_with_image(): void
    {
        Storage::fake('public');

        $brand = Brand::factory()->create();

        $payload = [
            'name' => $this->faker->unique()->name(),
            'description' => $this->faker->sentence(),
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.brands.update', $brand), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.brands.show', $brand));

        $this->assertDatabaseHas('brands', [
            'name' => $payload['name'],
            'description' => $payload['description'],
        ]);

        $this->assertCount(1, $brand->getMedia());
    }

    public function test_backoffice_brands_destroy_route(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.brands.destroy', $brand));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.brands.index'));

        $this->assertSoftDeleted('brands', [
            'id' => $brand->id,
            'name' => $brand->name,
            'description' => $brand->description,
        ]);
    }

    public function test_backoffice_brands_destroy_route_forever(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.brands.destroy', [$brand, 'forever' => true]));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.brands.index'));

        $this->assertDatabaseMissing('brands', [
            'id' => $brand->id,
            'name' => $brand->name,
            'description' => $brand->description,
        ]);
    }

    public function test_backoffice_brands_export_route(): void
    {

        Excel::fake();

        Brand::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.brands.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Brand::getExportFileName());
    }

    public function test_backoffice_brands_import_route_get_method(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.brands.import'));

        $response->assertOk();
    }

    public function test_backoffice_brands_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.brands.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('users.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}
