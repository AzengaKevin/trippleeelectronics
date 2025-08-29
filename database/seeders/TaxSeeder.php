<?php

namespace Database\Seeders;

use App\Models\Jurisdiction;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $jurisdiction = Jurisdiction::query()->first();

        collect([
            [
                'name' => 'VAT',
                'rate' => 16.00,
                'jurisdiction_id' => $jurisdiction?->id,
            ],
        ])->each(fn ($item) => Tax::query()->updateOrCreate([
            'name' => $item['name'],
        ], [
            'rate' => $item['rate'],
            'jurisdiction_id' => $item['jurisdiction_id'],
        ]));
    }
}
