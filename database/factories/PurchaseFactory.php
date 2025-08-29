<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount' => $amount = $this->faker->randomFloat(2, 10_000, 100_000),
            'shipping_amount' => $shippingAmount = $this->faker->randomFloat(2, 1_000, 10_000),
            'total_amount' => $amount + $shippingAmount,
        ];
    }
}
