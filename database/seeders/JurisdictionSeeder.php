<?php

namespace Database\Seeders;

use App\Models\Jurisdiction;
use Illuminate\Database\Seeder;

class JurisdictionSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            [
                'name' => 'Kenya',
            ],
        ])->each(fn ($attributes) => Jurisdiction::query()->updateOrCreate($attributes));
    }
}
