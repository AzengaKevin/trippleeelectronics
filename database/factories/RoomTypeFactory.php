<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => $this->faker->unique()->word(),
            'occupancy' => $this->faker->numberBetween(1, 4),
            'description' => $this->faker->sentence(),
            'active' => $this->faker->boolean(),
        ];
    }
}
