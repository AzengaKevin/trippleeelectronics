<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Building;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(
            permissions: [
                'access-backoffice',
                'browse-bookings',
                'create-bookings',
                'import-bookings',
                'update-bookings',
                'delete-bookings',
                'export-bookings',
            ]
        );
    }

    public function test_backoffice_bookings_index_route(): void
    {
        $this->withoutExceptionHandling();

        $buildings = Building::factory()->for(Property::factory())->count(2)->create();

        $buildings->map(fn (Building $building) => Room::factory()->for($building)->count(2)->create());

        $response = $this->actingAs($this->user)->get(route('backoffice.bookings.index'));

        $response->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/bookings/IndexPage')
                ->has('currentBuilding')
                ->has('buildings', 2)
                ->has('bookings.data', 2)
                ->has('params')
        );
    }

    public function test_backoffice_bookings_create_route(): void
    {
        $this->withoutExceptionHandling();

        $buildings = Building::factory()->for(Property::factory())->count(2)->create();

        $buildings->map(fn (Building $building) => Room::factory()->for($building)->count(2)->create());

        $response = $this->actingAs($this->user)->get(route('backoffice.bookings.create'));

        $response->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/bookings/CreatePage')
        );
    }
}
