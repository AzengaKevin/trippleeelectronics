<?php

namespace Database\Factories;

use App\Models\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(StockMovementType::options()),
            'quantity' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->sentence(),
            'cost_implication' => $this->faker->randomFloat(2, 1, 1000),
        ];
    }
}
