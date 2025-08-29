<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => $this->faker->randomDigitNotZero(),
            'price' => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
