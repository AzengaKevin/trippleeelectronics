<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attribute' => $this->faker->word(),
            'value' => $this->faker->unique()->word(),
            'name' => $this->faker->unique()->word(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomFloat(2, 0, 100),
            'description' => $this->faker->sentence(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'image_url' => $this->faker->imageUrl(),
        ];
    }
}
