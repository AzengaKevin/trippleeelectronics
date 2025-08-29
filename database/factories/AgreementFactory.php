<?php

namespace Database\Factories;

use App\Models\Enums\AgreementStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgreementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph(),
            'status' => AgreementStatus::PENDING->value,
            'since' => now(),
            'until' => now()->addYear(),
        ];
    }
}
