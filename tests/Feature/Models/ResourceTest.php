<?php

namespace Tests\Feature\Models;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_resource(): void
    {

        $attributes = [
            'name' => 'Users',
            'route_name' => 'users.index',
            'icon' => 'users',
            'order' => 1,
            'description' => 'List of users in the database',
            'is_active' => $this->faker->boolean(),
            'count' => 2,
            'required_permission' => 'user-browse',
            'morph_class' => 'user',
        ];

        $resource = Resource::query()->create($attributes);

        $this->assertNotNull($resource);

        $this->assertDatabaseHas('resources', $attributes);
    }

    public function test_creating_a_new_resource_from_factory(): void
    {
        $resource = Resource::factory()->create();

        $this->assertNotNull($resource);
    }

    public function test_resource_author_relationship_method(): void
    {
        $resource = Resource::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(User::class, $resource->author);

        $this->assertEquals($resource->author_user_id, $resource->author->id);
    }
}
