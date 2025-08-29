<?php

namespace Database\Factories;

use App\Models\Enums\TransactionMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transaction_method' => $this->faker->randomElement(TransactionMethod::options()),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'till' => $this->faker->numerify('######'),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('######'),
            'phone_number' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'reference' => null,
            'fee' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
