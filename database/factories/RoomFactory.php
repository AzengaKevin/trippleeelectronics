<?php

namespace Database\Factories;

use App\Models\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('ROOM-???'),
            'occupancy' => $this->faker->numberBetween(1, 4),
            'active' => $this->faker->boolean(),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'status' => $this->faker->randomElement(RoomStatus::options()),
        ];
    }
}
