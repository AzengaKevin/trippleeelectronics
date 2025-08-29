<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name(),
            'cost' => $cost = $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, $cost, $cost + 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'description' => $this->faker->sentence(),
            'quantity' => 0,
            'image_url' => $this->faker->imageUrl(),
        ];
    }
}
