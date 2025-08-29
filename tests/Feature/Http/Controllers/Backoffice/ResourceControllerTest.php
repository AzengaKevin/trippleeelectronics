<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ResourceControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-resources',
            'create-resources',
            'import-resources',
            'export-resources',
            'update-resources',
            'delete-resources',
        ]);
    }

    public function test_backoffice_resources_index_route(): void
    {
        Resource::factory()->count($resourcesCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.resources.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('resources.data', $resourcesCount)
        );
    }

    public function test_backoffice_resources_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.resources.create'));

        $response->assertStatus(200);
    }

    public function test_backoffice_resources_store_route(): void
    {
        $payload = [
            'name' => $this->faker->unique()->sentence(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.resources.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.resources.index'));

        $this->assertDatabaseHas('resources', [
            ...$payload,
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_resources_store_route_with_file(): void
    {
        Storage::fake('public');

        $payload = [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('resource.jpg'),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.resources.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.resources.index'));

        $resource = Resource::where('name', $payload['name'])->first();

        $this->assertNotNull($resource);

        $this->assertCount(1, $resource->getMedia());
    }

    public function test_backoffice_resources_show_route(): void
    {
        $resource = Resource::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.resources.show', $resource));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('resource')
                ->where('resource.id', $resource->id)
                ->where('resource.name', $resource->name)
                ->where('resource.description', $resource->description)
        );
    }

    public function test_backoffice_resources_edit_route(): void
    {
        $resource = Resource::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.resources.edit', $resource));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('resource')
                ->where('resource.id', $resource->id)
                ->where('resource.name', $resource->name)
                ->where('resource.description', $resource->description)
        );
    }

    public function test_backoffice_resources_update_route(): void
    {
        $resource = Resource::factory()->create();

        $payload = [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.resources.update', $resource), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.resources.show', $resource));

        $this->assertDatabaseHas('resources', $payload);
    }

    public function test_backoffice_resources_update_route_with_file(): void
    {
        Storage::fake('public');

        $resource = Resource::factory()->create();

        $payload = [
            'name' => $this->faker->unique()->sentence(),
            'description' => $this->faker->sentence(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('resource.jpg'),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.resources.update', $resource), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.resources.show', $resource));

        $this->assertDatabaseHas('resources', [
            'name' => $payload['name'],
            'description' => $payload['description'],
        ]);

        $this->assertCount(1, $resource->getMedia());
    }

    public function test_backoffice_resources_destroy_route(): void
    {
        $resource = Resource::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.resources.destroy', $resource));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.resources.index'));

        $this->assertSoftDeleted('resources', [
            'id' => $resource->id,
            'name' => $resource->name,
            'description' => $resource->description,
        ]);
    }

    public function test_backoffice_resources_destroy_route_forever(): void
    {
        $resource = Resource::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.resources.destroy', [$resource, 'forever' => true]));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.resources.index'));

        $this->assertDatabaseMissing('resources', [
            'id' => $resource->id,
            'name' => $resource->name,
            'description' => $resource->description,
        ]);
    }

    public function test_backoffice_resources_export_route(): void
    {
        Excel::fake();

        Resource::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.resources.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Resource::getExportFileName());
    }

    public function test_backoffice_resources_import_route_get_method(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.resources.import'));

        $response->assertOk();
    }

    public function test_backoffice_resources_import_route_post_method(): void
    {
        Excel::fake();

        Storage::fake('public');

        $response = $this->actingAs($this->user)->post(route('backoffice.resources.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('resources.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}
