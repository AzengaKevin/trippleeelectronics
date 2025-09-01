<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('BUILD-???'),
            'active' => $this->faker->boolean(),
        ];
    }
}
