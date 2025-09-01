<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('CODE-????'),
            'address' => $this->faker->address(),
            'active' => $this->faker->boolean(),
        ];
    }
}
