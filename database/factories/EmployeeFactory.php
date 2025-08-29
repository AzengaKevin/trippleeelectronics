<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
            'position' => $this->faker->jobTitle(),
            'department' => $this->faker->randomElement(['HR', 'IT', 'Finance', 'Marketing']),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'identification_number' => $this->faker->numerify('########'),
            'hire_date' => now()->toDateString(),
        ];
    }
}
