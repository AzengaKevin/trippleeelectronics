<?php

namespace Tests\Feature\Models;

use App\Models\Amenity;
use App\Models\Building;
use App\Models\Enums\RoomStatus;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_room_record_creation(): void
    {
        $property = Property::factory()->create();
        $building = Building::factory()->for($property)->create();
        $floor = Floor::factory()->for($building)->create();
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'room_type_id' => $roomType->id,
            'name' => $this->faker->word,
            'code' => $this->faker->word,
            'occupancy' => $this->faker->numberBetween(1, 4),
            'active' => $this->faker->boolean(),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'status' => $this->faker->randomElement(RoomStatus::options()),
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => $room->name,
            'code' => $room->code,
            'occupancy' => $room->occupancy,
            'active' => $room->active,
            'status' => $room->status,
            'price' => $room->price,
        ]);
    }

    public function test_creating_room_record_from_factory(): void
    {
        $property = Property::factory()->create();
        $building = Building::factory()->for($property)->create();
        $floor = Floor::factory()->for($building)->create();
        $roomType = RoomType::factory()->create();

        $room = Room::factory()->create([
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'room_type_id' => $roomType->id,
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => $room->name,
            'code' => $room->code,
            'occupancy' => $room->occupancy,
            'active' => $room->active,
            'status' => $room->status,
            'price' => $room->price,
        ]);
    }

    public function test_amenities_relationship(): void
    {
        $room = Room::factory()->for(Building::factory())->create();

        $amenity = Amenity::factory()->create();

        $room->amenities()->attach($amenity);

        $this->assertTrue($room->amenities->contains($amenity));
    }
}
