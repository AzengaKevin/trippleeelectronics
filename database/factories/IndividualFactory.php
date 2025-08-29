<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IndividualFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'id_number' => $this->faker->numerify('########'),
        ];
    }
}
