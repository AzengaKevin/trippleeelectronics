<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Building;
use App\Models\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(
            permissions: [
                'access-backoffice',
                'browse-rooms',
                'create-rooms',
                'update-rooms',
                'delete-rooms',
                'import-rooms',
                'export-rooms',
            ]
        );
    }

    public function test_backoffice_rooms_index_route(): void
    {
        $this->withoutExceptionHandling();

        Room::factory()->for(Building::factory())->count($roomsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.rooms.index'));

        $response->assertInertia(fn ($page) => $page->component('backoffice/rooms/IndexPage')->has('rooms.data', $roomsCount)->has('params'));
    }

    public function test_backoffice_rooms_create_route(): void
    {
        $this->withoutExceptionHandling();

        Building::factory()->count($buildingsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.rooms.create'));

        $response->assertInertia(fn ($page) => $page->component('backoffice/rooms/CreatePage')->has('buildings', $buildingsCount));
    }

    public function test_backoffice_rooms_store_route(): void
    {
        $this->withoutExceptionHandling();

        $building = Building::factory()->create();

        $roomType = RoomType::factory()->create();

        $payload = [
            'building' => $building->id,
            'room_type' => $roomType->id,
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('BUILD-???'),
            'active' => $this->faker->boolean(),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'status' => $this->faker->randomElement(RoomStatus::options()),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.rooms.store'), $payload);

        $response->assertRedirect(route('backoffice.rooms.index'));

        $this->assertDatabaseHas('rooms', [
            'building_id' => $building->id,
            'room_type_id' => $roomType->id,
            'name' => data_get($payload, 'name'),
            'code' => data_get($payload, 'code'),
            'active' => data_get($payload, 'active'),
            'price' => data_get($payload, 'price'),
        ]);
    }

    public function test_backoffice_rooms_show_route(): void
    {
        $this->withoutExceptionHandling();

        $room = Room::factory()->for(Building::factory())->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.rooms.show', $room));

        $response->assertInertia(fn ($page) => $page->component('backoffice/rooms/ShowPage')->has('room'));
    }

    public function test_backoffice_rooms_edit_route(): void
    {
        $this->withoutExceptionHandling();

        $room = Room::factory()->for(Building::factory())->create();

        Building::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.rooms.edit', $room));

        $response->assertInertia(fn ($page) => $page->component('backoffice/rooms/EditPage')->has('room')->has('buildings'));
    }

    public function test_backoffice_rooms_update_route(): void
    {
        $this->withoutExceptionHandling();

        $room = Room::factory()->for(Building::factory())->create();

        $building = Building::factory()->create();

        $roomType = RoomType::factory()->create();

        $payload = [
            'building' => $building->id,
            'room_type' => $roomType->id,
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('BUILD-???'),
            'active' => $this->faker->boolean(),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'status' => $this->faker->randomElement(RoomStatus::options()),
        ];

        $response = $this->actingAs($this->user)->put(route('backoffice.rooms.update', $room), $payload);

        $response->assertRedirect(route('backoffice.rooms.index'));

        $room->refresh();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'building_id' => $building->id,
            'room_type_id' => $roomType->id,
            'name' => data_get($payload, 'name'),
            'code' => data_get($payload, 'code'),
            'active' => data_get($payload, 'active') ? 1 : 0,
            'price' => data_get($payload, 'price'),
        ]);
    }

    public function test_backoffice_rooms_destroy_route(): void
    {
        $this->withoutExceptionHandling();

        $room = Room::factory()->for(Building::factory())->create();

        $response = $this->actingAs($this->user)->delete(route('backoffice.rooms.destroy', $room));

        $response->assertRedirect(route('backoffice.rooms.index'));

        $this->assertSoftDeleted('rooms', [
            'id' => $room->id,
        ]);
    }

    public function test_backoffice_rooms_export_route(): void
    {
        Excel::fake();

        Room::factory()->for(Building::factory())->count($roomsCount = 2)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.rooms.export'));

        $response->assertOk();

        Excel::assertDownloaded(Room::getExportFilename());
    }

    public function test_backoffice_rooms_import_route(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->user)->post(route('backoffice.rooms.import'), [
            'file' => UploadedFile::fake()->create('rooms.xlsx', 100),
        ]);

        $response->assertRedirect(route('backoffice.rooms.index'));

        $response->assertSessionHasNoErrors();
    }
}
