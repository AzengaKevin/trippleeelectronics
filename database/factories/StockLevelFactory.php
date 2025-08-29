<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockLevelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(1, 100),
        ];
    }
}
