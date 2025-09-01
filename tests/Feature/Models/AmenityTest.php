<?php

namespace Tests\Feature\Models;

use App\Models\Amenity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AmenityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_amenity_record(): void
    {
        $attributes = [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
        ];

        $amenity = Amenity::query()->create($attributes);

        $this->assertModelExists($amenity);

        $this->assertDatabaseHas('amenities', $attributes);
    }

    public function test_creating_an_amenity_via_factory(): void
    {
        $amenity = Amenity::factory()->create();

        $this->assertModelExists($amenity);
    }
}
