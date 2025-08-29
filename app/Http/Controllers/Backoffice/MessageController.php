<?php

namespace App\Http\Controllers\Backoffice;

use App\Events\NewMessageEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Message;
use App\Models\Thread;
use App\Services\MessageService;
use App\Services\ThreadService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(
        private readonly ThreadService $threadService,
        private readonly MessageService $messageService
    ) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'perPage');

        $threads = $this->threadService->get(...$params, withCount: ['messages'], user: $request->user());

        return Inertia::render('backoffice/messages/IndexPage', [
            'params' => $params,
            'threads' => $threads,
        ]);
    }

    public function store(StoreMessageRequest $storeMessageRequest)
    {
        $data = $storeMessageRequest->validated();

        try {

            $message = $this->messageService->create([
                ...$data,
                'user_id' => request()->user()->id,
            ]);

            broadcast(new NewMessageEvent($message))->toOthers();

            return $this->sendSuccessRedirect('Message created successfully.', url()->previous());
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to create message', $throwable);
        }
    }

    public function show(Request $request, Thread $thread)
    {

        $params = $request->only('query', 'perPage');

        $messages = $this->messageService->get(...$params, thread: $thread, currentUser: $request->user(), perPage: 48);

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

        return Inertia::render('backoffice/messages/ShowPage', [
            'params' => $params,
            'messages' => $messages,
            'thread' => $thread,
        ]);
    }

    public function read(Request $request, Message $message)
    {

        try {
            $this->messageService->markAsRead($message, $request->user());

            return $this->sendSuccessRedirect('Message marked as read successfully.', route('backoffice.messages.show', $message->thread));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to mark message as read', $throwable);
        }
    }
}
