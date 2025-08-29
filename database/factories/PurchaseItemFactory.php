<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => $this->faker->randomDigitNotZero(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
