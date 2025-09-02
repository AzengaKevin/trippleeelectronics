<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Building;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class BuildingControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(
            permissions: [
                'access-backoffice',
                'browse-buildings',
                'create-buildings',
                'read-buildings',
                'update-buildings',
                'delete-buildings',
                'import-buildings',
                'export-buildings',
            ]
        );
    }

    public function test_backoffice_building_index_route(): void
    {
        Building::factory()->for(Property::factory())->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.buildings.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/buildings/IndexPage')
                ->has('buildings')
                ->has('params')
                ->has('properties')
        );
    }

    public function test_backoffice_building_create_route(): void
    {
        $response = $this->actingAs($this->user)->get(route('backoffice.buildings.create'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/buildings/CreatePage')
                ->has('properties')
        );
    }

    public function test_backoffice_building_store_route(): void
    {
        $property = Property::factory()->create();

        $data = [
            'property' => $property->id,
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('BUILD-???'),
            'active' => $this->faker->boolean(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.buildings.store'), $data);

        $response->assertRedirect(route('backoffice.buildings.index'));

        $this->assertDatabaseHas('buildings', [
            'name' => data_get($data, 'name'),
            'code' => data_get($data, 'code'),
            'active' => data_get($data, 'active'),
            'property_id' => data_get($data, 'property'),
        ]);
    }

    public function test_backoffice_building_show_route(): void
    {
        $building = Building::factory()->for(Property::factory())->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.buildings.show', $building));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/buildings/ShowPage')
                ->has('building')
        );
    }

    public function test_backoffice_building_edit_route(): void
    {
        $building = Building::factory()->for(Property::factory())->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.buildings.edit', $building));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/buildings/EditPage')
                ->has('building')
                ->has('properties')
        );
    }

    public function test_backoffice_building_update_route(): void
    {
        $property = Property::factory()->create();

        $building = Building::factory()->for($property)->create();

        $data = [
            'property' => $property->id,
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('BUILD-???'),
            'active' => $this->faker->boolean(),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.buildings.update', $building), $data);

        $response->assertRedirect(route('backoffice.buildings.index'));

        $this->assertDatabaseHas('buildings', [
            'id' => $building->id,
            'name' => data_get($data, 'name'),
            'code' => data_get($data, 'code'),
            'active' => data_get($data, 'active'),
            'property_id' => data_get($data, 'property'),
        ]);
    }

    public function test_backoffice_building_destroy_route(): void
    {
        $building = Building::factory()->for(Property::factory())->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.buildings.destroy', $building));

        $response->assertRedirect(route('backoffice.buildings.index'));

        $this->assertSoftDeleted('buildings', [
            'id' => $building->id,
        ]);
    }

    public function test_backoffice_building_export_route(): void
    {
        Excel::fake();

        Building::factory()->for(Property::factory())->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.buildings.export'));

        $response->assertSessionHasNoErrors();

        Excel::assertDownloaded(Building::getExportFilename());
    }

    public function test_backoffice_building_import_route(): void
    {
        Excel::fake();

        $file = UploadedFile::fake()->create('buildings.xlsx');

        $response = $this->actingAs($this->user)->post(route('backoffice.buildings.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('backoffice.buildings.index'));

        $response->assertSessionHasNoErrors();
    }
}
