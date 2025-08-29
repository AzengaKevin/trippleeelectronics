<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(1, 3),
            'price' => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
