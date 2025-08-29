<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Carousel;
use App\Models\Enums\OrientationOption;
use App\Models\Enums\PositionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class CarouselControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-carousels',
            'create-carousels',
            'read-carousels',
            'update-carousels',
            'delete-carousels',
            'export-carousels',
            'import-carousels',
        ]);
    }

    public function test_backoffice_carousels_index_route()
    {
        Carousel::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.carousels.index'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($assert) => $assert->component('backoffice/carousels/IndexPage')->has('carousels.data'));
    }

    public function test_backoffice_carousels_create_route(): void
    {
        $response = $this->actingAs($this->admin)->get(route('backoffice.carousels.create'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->hasAll(['orientationOptions', 'positionOptions']));
    }

    public function test_backoffice_carousels_store_route(): void
    {
        $payload = [
            'orientation' => $this->faker->randomElement(OrientationOption::options()),
            'position' => $this->faker->randomElement(PositionOption::options()),
            'link' => $this->faker->url(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('backoffice.carousels.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('carousels', [
            'author_user_id' => $this->admin->id,
            ...$payload,
        ]);
    }

    public function test_backoffice_carousels_store_route_with_image(): void
    {
        Storage::fake('public');

        $payload = [
            'orientation' => $this->faker->randomElement(OrientationOption::options()),
            'position' => $this->faker->randomElement(PositionOption::options()),
            'link' => $this->faker->url(),
            'title' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'active' => true,
            'image' => UploadedFile::fake()->image('carousel.jpg'),
        ];

        $response = $this->actingAs($this->admin)->post(route('backoffice.carousels.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('carousels', [
            'title' => $payload['title'],
            'orientation' => $payload['orientation'],
            'position' => $payload['position'],
            'description' => $payload['description'],
            'active' => $payload['active'],
        ]);

        /** @var Carousel $carousel */
        $carousel = Carousel::query()->where('title', $payload['title'])->first();

        $this->assertCount(1, $carousel->getMedia());
    }

    public function test_backoffice_carousels_show_route(): void
    {
        $carousel = Carousel::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.carousels.show', compact('carousel')));

        $response->assertOk();

        $response->assertInertia(fn (AssertableInertia $assertableInertia) => $assertableInertia->has('carousel'));
    }

    public function test_backoffice_carousels_edit_route(): void
    {
        $carousel = Carousel::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.carousels.edit', compact('carousel')));

        $response->assertOk();

        $response->assertInertia(fn (AssertableInertia $assertableInertia) => $assertableInertia->hasAll(['carousel', 'orientationOptions', 'positionOptions']));
    }

    public function test_backoffice_carousels_update_route(): void
    {
        $carousel = Carousel::factory()->create();

        $payload = [
            'orientation' => $this->faker->randomElement(OrientationOption::options()),
            'position' => $this->faker->randomElement(PositionOption::options()),
            'link' => $this->faker->url(),
            'title' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'active' => false,
        ];

        $response = $this->actingAs($this->admin)->patch(route('backoffice.carousels.update', compact('carousel')), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('carousels', $payload);
    }

    public function test_backoffice_carousels_update_route_with_image(): void
    {
        Storage::fake('public');

        $carousel = Carousel::factory()->create();

        $payload = [
            'orientation' => $this->faker->randomElement(OrientationOption::options()),
            'position' => $this->faker->randomElement(PositionOption::options()),
            'link' => $this->faker->url(),
            'title' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'active' => true,
            'image' => UploadedFile::fake()->image('carousel.jpg'),
        ];

        $response = $this->actingAs($this->admin)->patch(route('backoffice.carousels.update', compact('carousel')), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('carousels', [
            'title' => data_get($payload, 'title'),
            'orientation' => data_get($payload, 'orientation'),
            'position' => data_get($payload, 'position'),
            'description' => data_get($payload, 'description'),
            'active' => data_get($payload, 'active'),
        ]);

        $this->assertCount(1, $carousel->getMedia());
    }

    public function test_backoffice_carousels_destroy_route(): void
    {
        $carousel = Carousel::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('backoffice.carousels.destroy', compact('carousel')));

        $response->assertRedirect();

        $this->assertSoftDeleted($carousel);
    }

    public function test_backoffice_carousels_export_route(): void
    {
        Excel::fake();

        Carousel::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('backoffice.carousels.export'));

        $response->assertOk();

        Excel::assertDownloaded(Carousel::getExportFilename());
    }

    public function test_backoffice_carousels_import_route_get_method(): void
    {
        $response = $this->actingAs($this->admin)->get(route('backoffice.carousels.import'));

        $response->assertOk();
    }

    public function test_backoffice_carousels_import_route_post_method(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->admin)->post(route('backoffice.carousels.import'), [
            'file' => UploadedFile::fake()->create('carousels.xlsx', 1024),
        ]);

        $response->assertStatus(302);
    }
}
