<?php

namespace Tests\Feature\Models;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_be_created()
    {
        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => str('+254')->append($this->faker->randomElement([1, 7]))->append($this->faker->numerify('#########'))->value(),
        ];

        $user = User::query()->create([
            ...$attributes,
            'password' => Hash::make($password = $this->faker->password(minLength: 8)),
        ]);

        $this->assertNotNull($user);

        $this->assertDatabaseHas('users', $attributes);

        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function test_a_user_can_be_assigned_a_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $role = Role::query()->create(['name' => 'Admin']);

        $user->assignRole($role);

        $this->assertContains($role->id, $user->fresh()->roles->pluck('id')->all());
    }

    public function test_user_stores_relationship_method(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $store = Store::factory()->create();

        $user->stores()->attach($store);

        $this->assertContains($user->id, $store->fresh()->users->pluck('id')->all());
    }

    public function test_user_author_relationship_method(): void
    {
        /** @var User $user */
        $user = User::factory()->for(User::factory(), 'author')->create();

        $this->assertInstanceOf(\App\Models\User::class, $user->author);

        $this->assertEquals($user->author_user_id, $user->author->id);
    }

    public function test_user_messages_relationship_method(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $message = $user->messages()->create([
            'message' => 'This is a test message.',
        ]);

        $this->assertInstanceOf(\App\Models\Message::class, $message);
        $this->assertEquals($user->id, $message->user_id);
    }
}
