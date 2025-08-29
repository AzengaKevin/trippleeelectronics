<?php

namespace Database\Seeders;

use App\Models\Individual;
use Illuminate\Database\Seeder;

class IndividualSeeder extends Seeder
{
    public function run(): void
    {
        Individual::factory()
            ->count(10)
            ->create();
    }
}
