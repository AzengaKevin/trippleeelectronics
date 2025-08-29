<?php

namespace Database\Factories;

use App\Models\Enums\OrientationOption;
use App\Models\Enums\PositionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarouselFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'position' => $this->faker->randomElement(PositionOption::options()),
            'orientation' => $this->faker->randomElement(OrientationOption::options()),
            'link' => $this->faker->url(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'active' => $this->faker->boolean(),
        ];
    }
}
