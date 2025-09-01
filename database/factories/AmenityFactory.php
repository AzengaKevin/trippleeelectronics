<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AmenityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
        ];
    }
}
