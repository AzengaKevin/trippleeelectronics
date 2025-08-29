<?php

namespace Tests\Feature\Models;

use App\Models\OrganizationCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrganizationCategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_organization_category_can_be_created()
    {

        $attributes = [
            'name' => $this->faker->unique()->word(),
        ];

        $category = OrganizationCategory::query()->create($attributes);

        $this->assertNotNull($category);

        $this->assertDatabaseHas('organization_categories', $attributes);
    }

    public function test_organization_category_can_be_created_from_factory()
    {
        $category = OrganizationCategory::factory()->create();

        $this->assertNotNull($category);

        $this->assertDatabaseHas('organization_categories', [
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }

    public function test_organization_category_author_relationship_method()
    {
        $category = OrganizationCategory::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(User::class, $category->author);

        $this->assertEquals($category->author_user_id, $category->author->id);
    }
}
