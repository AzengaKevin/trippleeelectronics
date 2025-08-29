<?php

namespace Tests\Feature\Models;

use App\Models\Group;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ThreadTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_creating_a_new_thread(): void
    {
        $user = User::factory()->create();

        $attributes = [
            'author_user_id' => $user->id,
            'last_updated_at' => now()->toDateTimeString(),
        ];

        $thread = Thread::query()->create($attributes);

        $this->assertNotNull($thread);

        $this->assertDatabaseHas('threads', $attributes);
    }

    public function test_creating_a_new_thread_from_factory(): void
    {
        $thread = Thread::factory()->create();

        $this->assertNotNull($thread);

        $this->assertEquals($thread->author_user_id, $thread->author->id);
    }

    public function test_creating_a_group_thread(): void
    {

        $user = User::factory()->create();

        $group = Group::factory()->create();

        $group->users()->attach($user);

        $attributes = [
            'name' => $group->name,
            'author_user_id' => $user->id,
            'group_id' => $group->id,
            'last_updated_at' => now()->toDateTimeString(),
        ];

        $thread = Thread::query()->create($attributes);

        $this->assertNotNull($thread);

        $this->assertEquals($thread->group_id, $group->id);
    }

    public function test_thread_belongs_to_users(): void
    {
        $thread = Thread::factory()->create();

        $user = User::factory()->create();

        $thread->users()->attach($user);

        $user2 = User::factory()->create();

        $thread->users()->attach($user2);

        $this->assertTrue($thread->users->contains($user));

        $this->assertTrue($thread->users->contains($user2));
    }

    public function test_thread_messages_relationship(): void
    {
        $thread = Thread::factory()->create();

        $message1 = Message::factory()->for(User::factory())->create(['thread_id' => $thread->id]);
        $message2 = Message::factory()->for(User::factory())->create(['thread_id' => $thread->id]);

        $this->assertTrue($thread->messages->contains($message1));
        $this->assertTrue($thread->messages->contains($message2));
    }
}
