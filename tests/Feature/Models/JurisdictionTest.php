<?php

namespace Tests\Feature\Models;

use App\Models\Jurisdiction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JurisdictionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_jurisdiction(): void
    {
        $attributes = [
            'name' => $this->faker->city(),
        ];

        $jurisdiction = Jurisdiction::query()->create($attributes);

        $this->assertNotNull($jurisdiction);

        $this->assertDatabaseHas('jurisdictions', $attributes);
    }

    public function test_creating_jurisdiction_from_factory(): void
    {
        $jurisdiction = Jurisdiction::factory()->create();

        $this->assertNotNull($jurisdiction);

        $this->assertDatabaseHas('jurisdictions', $jurisdiction->only('name'));
    }
}
