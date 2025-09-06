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
            'guests_count' => $this->faker->numberBetween(1, 5),
            'rooms_count' => $this->faker->numberBetween(0, 5),
            'tendered_amount' => $this->faker->randomFloat(2, 1000, 5000),
            'balance_amount' => $this->faker->randomFloat(2, 100, 999),
        ];
    }
}
