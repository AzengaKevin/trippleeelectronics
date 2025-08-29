<?php

namespace Database\Factories;

use App\Models\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(PaymentStatus::options()),
            'amount' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
