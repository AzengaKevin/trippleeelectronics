<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Property;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        $properties = Property::query()->get();

        $properties->each(function ($property) {

            Building::query()->create(['property_id' => $property->id, 'name' => $property->name.' Building 1']);
        });
    }
}
