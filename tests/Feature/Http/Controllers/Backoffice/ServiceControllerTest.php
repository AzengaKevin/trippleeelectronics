<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-services',
            'create-services',
            'update-services',
            'delete-services',
            'export-services',
            'import-services',
        ]);
    }

    public function test_backoffice_services_index_route(): void
    {
        Service::factory()->count($servicesCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.services.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('services.data', $servicesCount)
        );
    }

    public function test_backoffice_services_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.services.create'));

        $response->assertStatus(200);
    }

    public function test_backoffice_services_store_route(): void
    {
        $payload = [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.services.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.services.index'));

        $this->assertDatabaseHas('services', [
            ...$payload,
            'author_user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_services_store_route_with_image(): void
    {

        Storage::fake('public');

        $payload = [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'image' => \Illuminate\Http\UploadedFile::fake()->image('service.jpg'),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.services.store'), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.services.index'));

        $service = Service::where('title', $payload['title'])->first();

        $this->assertNotNull($service);

        $this->assertCount(1, $service->getMedia());
    }

    public function test_backoffice_services_show_route(): void
    {
        $service = Service::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.services.show', $service));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('service')
                ->where('service.id', $service->id)
                ->where('service.title', $service->title)
                ->where('service.description', $service->description)
        );
    }

    public function test_backoffice_services_edit_route(): void
    {
        $service = Service::factory()->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.services.edit', $service));

        $response->assertStatus(200);

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('service')
                ->where('service.id', $service->id)
                ->where('service.title', $service->title)
                ->where('service.description', $service->description)
        );
    }

    public function test_backoffice_services_update_route(): void
    {
        $service = Service::factory()->create();

        $payload = [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.services.update', $service), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.services.show', $service));

        $this->assertDatabaseHas('services', $payload);
    }

    public function test_backoffice_services_update_route_with_image(): void
    {
        Storage::fake('public');

        $service = Service::factory()->create();

        $payload = [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.services.update', $service), $payload);

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.services.show', $service));

        $this->assertDatabaseHas('services', [
            'title' => $payload['title'],
            'description' => $payload['description'],
        ]);

        $this->assertCount(1, $service->getMedia());
    }

    public function test_backoffice_services_destroy_route(): void
    {
        $service = Service::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.services.destroy', $service));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.services.index'));

        $this->assertSoftDeleted('services', [
            'id' => $service->id,
            'title' => $service->title,
            'description' => $service->description,
        ]);
    }

    public function test_backoffice_services_destroy_route_forever(): void
    {
        $service = Service::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.services.destroy', [$service, 'forever' => true]));

        $response->assertStatus(302);

        $response->assertRedirect(route('backoffice.services.index'));

        $this->assertDatabaseMissing('services', [
            'id' => $service->id,
            'title' => $service->title,
            'description' => $service->description,
        ]);
    }

    public function test_backoffice_services_export_route(): void
    {

        Excel::fake();

        Service::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.services.export'));

        $response->assertStatus(200);

        Excel::assertDownloaded(Service::getExportFileName());
    }

    public function test_backoffice_services_import_route_get_method(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.services.import'));

        $response->assertOk();
    }

    public function test_backoffice_services_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.services.import'), [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('users.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}
