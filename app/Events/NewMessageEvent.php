<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {

        $this->message->loadMissing('user');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("threads.{$this->message->thread_id}"),
        ];
    }

    public function broadcastWith(): array
    {

        return [
            'message' => $this->message,
        ];
    }
}
