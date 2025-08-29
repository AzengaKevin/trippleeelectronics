<?php

namespace Tests\Feature\Models;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_create_message_record(): void
    {
        $attriutes = [
            'user_id' => $this->user->id,
            'message' => $this->faker->sentence(),
        ];

        $message = Message::query()->create($attriutes);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'user_id' => $this->user->id,
            'message' => $attriutes['message'],
        ]);
    }

    public function test_message_belongs_to_user(): void
    {
        $message = Message::factory()->create(['user_id' => $this->user->id]);

        $this->assertInstanceOf(User::class, $message->user);

        $this->assertEquals($this->user->id, $message->user->id);
    }

    public function test_creating_a_thread_message(): void
    {

        $thread = Thread::factory()->create();

        $message = Message::factory()->create(['user_id' => $this->user->id, 'thread_id' => $thread->id]);

        $this->assertInstanceOf(Thread::class, $message->thread);

        $this->assertEquals($thread->id, $message->thread->id);
    }
}
