<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run()
    {
        \App\Models\Organization::factory(10)->create();
    }
}
