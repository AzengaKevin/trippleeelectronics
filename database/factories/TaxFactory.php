<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaxFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'rate' => $this->faker->randomFloat(2, 0, 100),
            'type' => $this->faker->word(),
            'is_compound' => $this->faker->boolean(),
            'is_inclusive' => $this->faker->boolean(),
        ];
    }
}
