<?php

namespace Tests\Feature\Models;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_group(): void
    {
        $owner = User::factory()->create();

        $attributes = [
            'author_user_id' => $owner->id,
            'name' => $this->faker->sentence(),
        ];

        $group = Group::query()->create($attributes);

        $this->assertNotNull($group);

        $this->assertDatabaseHas('groups', $attributes);

        $this->assertNotNull($group->author);
    }

    public function test_group_user_relationship_method(): void
    {
        $group = Group::factory()->create();

        $users = User::factory()->count(2)->create();

        $group->users()->sync($users);

        $this->assertEquals($users->count(), $group->users()->count());
    }
}
