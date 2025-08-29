<?php

namespace Tests\Feature\Models;

use App\Models\Individual;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndividualTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_an_individual_record_can_be_created(): void
    {
        $attributes = [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'id_number' => $this->faker->numerify('########'),
        ];

        $individual = Individual::query()->create($attributes);

        $this->assertNotNull($individual);

        $this->assertDatabaseHas('individuals', $attributes);
    }

    public function test_an_individual_record_with_can_be_created(): void
    {
        $organization = Organization::factory()->create();

        $attributes = [
            'organization_id' => $organization->id,
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address(),
            'kra_pin' => str('A')->append($this->faker->numerify('########'))->append($this->faker->randomElement())->upper()->value(),
            'id_number' => $this->faker->numerify('########'),
        ];

        $individual = Individual::query()->create($attributes);

        $this->assertNotNull($individual->organization);

        $this->assertNotNull($individual);

        $this->assertDatabaseHas('individuals', $attributes);
    }

    public function test_creating_an_individual_record_from_factory(): void
    {
        $individual = Individual::factory()->create();

        $this->assertNotNull($individual);
    }

    public function test_individual_author_relationship(): void
    {
        $individual = Individual::factory()->for(User::factory(), 'author')->create();

        $this->assertNotNull($individual->author);

        $this->assertInstanceOf(User::class, $individual->author);

        $this->assertEquals($individual->author->id, $individual->author_user_id);
    }
}
