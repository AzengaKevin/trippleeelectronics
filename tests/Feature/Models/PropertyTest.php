<?php

namespace Tests\Feature\Models;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_property_record(): void
    {
        $attributes = [
            'name' => $this->faker->sentence(),
            'code' => $this->faker->lexify('CODE-????'),
            'address' => $this->faker->address(),
            'active' => $this->faker->boolean(),
        ];

        $property = Property::query()->create($attributes);

        $this->assertNotNull($property);

        $this->assertDatabaseHas('properties', $attributes);
    }

    public function test_creating_a_property_record_from_factory(): void
    {
        $property = Property::factory()->create();

        $this->assertNotNull($property);

        $this->assertInstanceOf(Property::class, $property);
    }
}
