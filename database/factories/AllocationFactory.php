<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AllocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'start' => $this->faker->dateTimeBetween('2025-09-01 14:00:00', '2025-09-01 15:00:00'),
            'end' => $this->faker->dateTimeBetween('2025-09-01 15:00:00', '2025-09-01 16:00:00'),
            'occupants' => $this->faker->numberBetween(1, 4),
        ];
    }
}
