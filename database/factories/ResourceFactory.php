<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'order' => $this->faker->numberBetween(1, 20),
            'description' => fake()->sentence(),
            'is_active' => $this->faker->boolean(),
            'count' => $this->faker->numberBetween(10, 1000),
        ];
    }
}
