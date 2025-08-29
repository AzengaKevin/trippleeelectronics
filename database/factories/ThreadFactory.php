<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThreadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'author_user_id' => User::factory(),
        ];
    }
}
