<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'pos_name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'pos_description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
