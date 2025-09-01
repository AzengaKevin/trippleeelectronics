<?php

namespace Database\Factories;

use App\Models\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(ReservationStatus::options()),
            'checkin_date' => $this->faker->date(),
            'checkout_date' => $this->faker->date(),
            'adults' => $this->faker->numberBetween(1, 5),
            'children' => $this->faker->numberBetween(0, 5),
            'infants' => $this->faker->numberBetween(0, 5),
        ];
    }
}
