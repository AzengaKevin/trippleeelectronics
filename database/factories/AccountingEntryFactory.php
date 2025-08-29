<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountingEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
        ];
    }
}
