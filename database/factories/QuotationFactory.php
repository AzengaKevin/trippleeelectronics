<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount' => $amount = $this->faker->randomFloat(2, 0, 1000),
            'shipping_amount' => null,
            'tax_amount' => null,
            'discount_amount' => null,
            'total_amount' => $amount,
            'notes' => $this->faker->text(100),
        ];
    }
}
