<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SuspensionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'from' => now()->subWeek()->toDateString(),
            'to' => now()->addWeek()->toDateString(),
            'reason' => $this->faker->paragraph(),
        ];
    }
}
