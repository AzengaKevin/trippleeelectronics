<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'short_name' => str($this->faker->lexify('???'))->upper()->value(),
            'phone' => $this->faker->phoneNumber(),
            'paybill' => $this->faker->numerify('######'),
            'account_number' => $this->faker->numerify('ACC-######'),
            'till' => $this->faker->numerify('######'),
            'kra_pin' => $this->faker->numerify('KRA-######'),
            'address' => $this->faker->streetAddress(),
            'location' => $this->faker->city(),
        ];
    }
}
