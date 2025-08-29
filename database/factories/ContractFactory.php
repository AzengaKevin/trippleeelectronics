<?php

namespace Database\Factories;

use App\Models\Enums\ContractStatus;
use App\Models\Enums\ContractType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contract_type' => $this->faker->randomElement(ContractType::options()),
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'salary' => $this->faker->numberBetween(20000, 40000),
            'status' => $this->faker->randomElement(ContractStatus::options()),
        ];
    }
}
