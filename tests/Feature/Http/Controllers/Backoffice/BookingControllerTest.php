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
                'access-booking',
            ]
        );
    }

    public function test_backoffice_booking_show_route(): void
    {
        $this->withoutExceptionHandling();

        $buildings = Building::factory()->for(Property::factory())->count(2)->create();

        $buildings->map(fn (Building $building) => Room::factory()->for($building)->count(2)->create());

        $response = $this->actingAs($this->user)->get(route('backoffice.booking.show'));

        $response->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/booking/ShowPage')
                ->has('currentBuilding')
                ->has('buildings', 2)
                ->has('bookings.data', 2)
                ->has('params')
        );
    }
}
