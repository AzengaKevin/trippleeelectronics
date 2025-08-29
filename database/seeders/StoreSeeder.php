<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        Store::query()->create([
            'name' => 'Tripple e electronics',
        ]);
    }
}
