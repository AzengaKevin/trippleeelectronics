<?php

namespace Tests\Feature\Http\Controllers\Backoffice;

use App\Events\NewMessageEvent;
use App\Models\Group;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Feature\Traits\CreateAuthorizedUser;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use CreateAuthorizedUser, RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(permissions: [
            'access-backoffice',
            'browse-messages',
            'create-messages',
        ]);
    }

    public function test_backoffice_messages_index_route(): void
    {

        $group = Group::factory()->create();

        $group->users()->attach($this->user);

        Thread::factory()->for($group)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.messages.index'));

        $response->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/messages/IndexPage')
                ->has('threads')
                ->has('params')
        );
    }

    public function test_backoffice_messages_store_route(): void
    {

        Event::fake();

        $thread = Thread::factory()->create();

        $this->user->threads()->attach($thread);

        $thread->users()->attach(User::factory()->create());

        $payload = [
            'thread_id' => $thread->id,
            'message' => $this->faker->sentence(),
        ];

        $response = $this->actingAs($this->user)->post(route('backoffice.messages.store'), $payload);

        $response->assertRedirect();

        Event::assertDispatched(NewMessageEvent::class, fn ($event) => $event->message->thread_id === $payload['thread_id']);

        $this->assertDatabaseHas('messages', [
            'thread_id' => $payload['thread_id'],
            'message' => $payload['message'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_backoffice_messages_show_route(): void
    {
        $thread = Thread::factory()->create();

        $this->user->threads()->attach($thread);

        Message::factory()->for($thread)->for($this->user)->create();

        $response = $this->actingAs($this->user)->get(route('backoffice.messages.show', $thread));

        $response->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('backoffice/messages/ShowPage')
                ->has('thread')
                ->has('messages')
                ->has('params')
        );
    }

    public function test_backoffice_messages_read_route(): void
    {
        $thread = Thread::factory()->create();

        $this->user->threads()->attach($thread);

        $message = Message::factory()->for($thread)->for(User::factory())->create();

        $response = $this->actingAs($this->user)->put(route('backoffice.messages.read', $message));

        $response->assertRedirect();

        $this->assertDatabaseHas('message_read', [
            'message_id' => $message->id,
            'user_id' => $this->user->id,
        ]);
    }
}
