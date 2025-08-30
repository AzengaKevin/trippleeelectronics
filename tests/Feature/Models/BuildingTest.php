<?php

namespace Tests\Feature\Models;

use App\Models\Building;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuildingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_building_record(): void
    {
        $property = Property::factory()->create();

        $attributes = [
            'property_id' => $property->id,
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('BUILD-???'),
            'active' => $this->faker->boolean(),
        ];

        $building = Building::query()->create($attributes);

        $this->assertModelExists($building);

        $this->assertNotNull($building->property);

        $this->assertInstanceOf(Property::class, $building->property);
    }
}
