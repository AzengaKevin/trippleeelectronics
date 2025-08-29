<?php

namespace Database\Factories;

use App\Models\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'type' => $this->faker->randomElement(AccountType::options()),
            'author_user_id' => User::factory(),
        ];
    }
}
