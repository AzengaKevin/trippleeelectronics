<?php

namespace Tests\Feature\Models;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FloorTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_floor_record(): void
    {

        $building = Building::factory()->create();

        $attributes = [
            'building_id' => $building->id,
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('FLOOR-???'),
            'active' => $this->faker->boolean(),
        ];

        $floor = Floor::query()->create($attributes);

        $this->assertModelExists($floor);

        $this->assertNotNull($floor->building);

        $this->assertInstanceOf(Building::class, $floor->building);
    }

    public function test_creating_a_floor_from_the_factory(): void
    {
        $building = Building::factory()->create();

        $floor = Floor::factory()->for($building)->create();

        $this->assertModelExists($floor);

        $this->assertNotNull($floor->building);

        $this->assertInstanceOf(Building::class, $floor->building);
    }
}
