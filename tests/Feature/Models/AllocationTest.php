<?php

namespace Tests\Feature\Models;

use App\Models\Allocation;
use App\Models\Building;
use App\Models\Individual;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AllocationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_allocation_record(): void
    {

        $property = Property::factory()->create();

        $building = Building::factory()->for($property)->create();

        $roomType = RoomType::factory()->create();

        $room = Room::factory()->for($building)->create();

        $reservation = Reservation::factory()->for($property)->for(Individual::factory(), 'primaryIndividual')->for(User::factory(), 'author')->create();

        $attributes = [
            'reservation_id' => $reservation->id,
            'room_type_id' => $roomType->id,
            'room_id' => $room->id,
            'date' => $this->faker->date(),
            'start' => $this->faker->dateTimeBetween('2025-09-01 14:00:00', '2025-09-01 15:00:00'),
            'end' => $this->faker->dateTimeBetween('2025-09-01 15:00:00', '2025-09-01 16:00:00'),
            'occupancy' => $this->faker->numberBetween(1, 4),
            'status' => 'confirmed',
        ];

        $allocation = Allocation::query()->create($attributes);

        $this->assertNotNull($allocation);

        $this->assertDatabaseHas('allocations', $attributes);
    }

    public function test_creating_a_new_allocation_from_factory(): void
    {

        $property = Property::factory()->create();

        $building = Building::factory()->for($property)->create();

        $roomType = RoomType::factory()->create();

        $room = Room::factory()->for($building)->create();

        $reservation = Reservation::factory()->for($property)->for(Individual::factory(), 'primaryIndividual')->for(User::factory(), 'author')->create();

        $allocation = Allocation::factory()->for($reservation)->for($roomType)->for($room)->create();

        $this->assertNotNull($allocation);
    }
}
