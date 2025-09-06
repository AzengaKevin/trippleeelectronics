<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {

        $data = [
            [
                'name' => 'Greenview Homes',
            ],
            [
                'name' => 'Normad Homes',
            ],
            [
                'name' => 'Riverview Homes',
            ],
        ];

        collect($data)->each(function ($property) {

            Property::query()->create($property);
        });
    }
}
