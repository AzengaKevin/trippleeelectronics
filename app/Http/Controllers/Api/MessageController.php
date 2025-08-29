<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private readonly MessageService $messageService) {}

    public function index(Request $request)
    {

        $params = $request->only('before');

        $before = null;

        if ($beforeId = data_get($params, 'before')) {

            $before = Message::query()->with('thread')->findOrFail($beforeId);
        }

        $messages = $this->messageService->get(thread: $before?->thread, before: $before, currentUser: $request->user(), perPage: 48);

        $messages = $messages->through(function ($message) {

            $avatar = $message->user->getFirstMedia('avatar');

            $avatarUrl = $avatar ? $avatar->getUrl('preview') : "https://ui-avatars.com/api/?name={$message->user->name}&background=1E2A44&color=FFD700?size=128&rounded=true";

            return [
                'id' => $message->id,
                'message' => $message->message,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                    'email' => $message->user->email,
                    'avatar_url' => $avatarUrl,
                ],
                'created_at' => $message->created_at->toDateTimeString(),
                'read_at' => $message->read_at,
            ];
        });

        return new MessageResource($messages);
    }
}
