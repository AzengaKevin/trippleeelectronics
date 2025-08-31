<?php

namespace Tests\Feature\Models;

use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoomTypeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_room_type_record_creation()
    {

        $attributes = [
            'name' => $this->faker->word(),
            'code' => $this->faker->unique()->word(),
            'occupancy' => $this->faker->numberBetween(1, 4),
            'description' => $this->faker->sentence(),
            'active' => $this->faker->boolean(),
        ];

        $roomType = RoomType::query()->create($attributes);

        $this->assertDatabaseHas('room_types', [
            'name' => $roomType->name,
            'description' => $roomType->description,
            'code' => $roomType->code,
            'occupancy' => $roomType->occupancy,
            'active' => $roomType->active,
        ]);
    }

    public function test_creating_room_type_from_factory(): void
    {
        $roomType = RoomType::factory()->create();

        $this->assertDatabaseHas('room_types', [
            'id' => $roomType->id,
            'name' => $roomType->name,
            'code' => $roomType->code,
            'occupancy' => $roomType->occupancy,
            'description' => $roomType->description,
            'active' => $roomType->active,
        ]);
    }
}
