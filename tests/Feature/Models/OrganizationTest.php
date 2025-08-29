<?php

namespace Tests\Feature\Models;

use App\Models\Organization;
use App\Models\OrganizationCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_organization_record(): void
    {

        $category = OrganizationCategory::factory()->create();

        $attributes = [
            'organization_category_id' => $category->id,
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('########'))->value(),
            'address' => $this->faker->address,
            'kra_pin' => str('P')->append($this->faker->unique()->numerify('#########'))->append($this->faker->randomLetter())->upper()->value(),
        ];

        $organization = Organization::query()->create($attributes);

        $this->assertNotNull($organization->organizationCategory);

        $this->assertNotNull($organization);

        $this->assertDatabaseHas('organizations', $attributes);
    }

    public function test_creating_a_new_organization_record_from_factory(): void
    {
        $organization = Organization::factory()->create();

        $this->assertNotNull($organization);
    }

    public function test_organization_organization_category_relationship_method(): void
    {
        $category = OrganizationCategory::factory()->create();

        $organization = Organization::factory()->create(['organization_category_id' => $category->id]);

        $this->assertInstanceOf(OrganizationCategory::class, $organization->organizationCategory);
    }

    public function test_organization_author_relationship_method(): void
    {
        $user = \App\Models\User::factory()->create();

        $organization = Organization::factory()->create(['author_user_id' => $user->id]);

        $this->assertInstanceOf(\App\Models\User::class, $organization->author);
    }
}
