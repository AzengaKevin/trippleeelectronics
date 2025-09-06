<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Models\Building;
use App\Models\Individual;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-reservations',
            'create-reservations',
            'update-reservations',
            'delete-reservations',
            'browse-bookings',
            'create-bookings',
            'import-bookings',
            'update-bookings',
            'delete-bookings',
            'export-bookings',
        ]);
    }

    public function test_backoffice_reservations_store_route(): void
    {

        $individual = Individual::factory()->create();

        $room = Room::factory()->for(Building::factory()->for(Property::factory()))->create();

        $payload = [
            'guests' => [
                [
                    'id' => $individual->id,
                    'name' => $individual->name,
                    'email' => $individual->email,
                    'phone' => $individual->phone,
                    'address' => $individual->address,
                    'kra_pin' => $individual->kra_pin,
                    'id_number' => $individual->id_number,
                ],
            ],
            'allocations' => [
                [
                    'room' => $room->id,
                    'start' => now()->toDateString(),
                    'end' => now()->addDays(2)->toDateString(),
                    'occupancy' => 1,
                    'price' => $price = 4500.00,
                    'discount' => 0.00,
                    'amount' => $price * 2,
                ],
            ],
            'total_amount' => $price * 2,
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDays(2)->toDateString(),
            'guests_count' => 1,
            'rooms_count' => 1,
            'tendered_amount' => $price * 2,
            'balance_amount' => 0.00,
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.reservations.store'), $payload);

        /** @var Reservation $reservation */
        $reservation = Reservation::query()->where([
            ['author_user_id', $this->user->id],
            ['primary_individual_id', $individual->id],
        ])->first();

        $this->assertNotNull($reservation);

        $this->assertEquals($reservation->individuals()->count(), count($payload['guests']));

        $individual = $reservation->individuals()->first();

        $this->assertEquals($payload['guests'][0]['id'], $individual->id);
        $this->assertEquals($payload['guests'][0]['email'], $individual->email);
        $this->assertEquals($payload['guests'][0]['phone'], $individual->phone);
        $this->assertEquals($payload['guests'][0]['address'], $individual->address);
        $this->assertEquals($payload['guests'][0]['id_number'], $individual->id_number);
        $this->assertEquals($payload['guests'][0]['kra_pin'], $individual->kra_pin);

        $this->assertEquals($reservation->allocations()->count(), count(data_get($payload, 'allocations')));

        $response->assertRedirect();
    }
}
