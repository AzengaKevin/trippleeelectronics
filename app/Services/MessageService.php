<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageRead;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function get(
        ?string $query = null,
        ?int $perPage = 36,
        ?int $limit = null,
        ?array $with = ['user'],
        ?Thread $thread = null,
        ?Message $before = null,
        ?User $currentUser = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {
        $messageQuery = Message::query();

        $messageQuery->select('messages.*');

        $messageQuery->addSelect([
            'read_at' => MessageRead::query()
                ->select('message_read.created_at')
                ->whereColumn('message_read.message_id', 'messages.id')
                ->where('message_read.user_id', $currentUser?->id)
                ->limit(1),
        ]);

        $messageQuery->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('message', 'like', "%{$query}%")
                ->orWhereHas('user', function ($innerQuery) use ($query) {
                    $innerQuery->where('name', 'like', "%{$query}%");
                });
        });

        $messageQuery->when($with, function ($queryBuilder) use ($with) {
            return $queryBuilder->with($with);
        });

        $messageQuery->when($limit, function ($queryBuilder) use ($limit) {
            return $queryBuilder->limit($limit);
        });

        $messageQuery->when($thread, function ($queryBuilder) use ($thread) {
            return $queryBuilder->where('thread_id', $thread->id);
        });

        $messageQuery->when($before, function ($queryBuilder) use ($before) {

            return $queryBuilder->where('created_at', '<', $before->created_at);
        });

        $messageQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $messageQuery->get()
            : $messageQuery->paginate(perPage: $perPage);
    }

    public function create(array $data): Message
    {

        return DB::transaction(function () use ($data) {

            $attributes = [
                'thread_id' => data_get($data, 'thread_id'),
                'message' => data_get($data, 'message'),
                'user_id' => data_get($data, 'user_id'),
            ];

            return Message::query()->create($attributes);
        });
    }

    public function markAsRead(Message $message, User $user): void
    {
        $message->userReads()->syncWithoutDetaching($user->id);
    }

    public function fetchUnreadMessagesCount(User $user, ?Thread $thread = null): int
    {

        return Message::query()
            ->whereDoesntHave('userReads', fn ($query) => $query->where('user_id', $user->id))
            ->whereHas('thread', function ($query) use ($user) {
                $query->whereHas('users', function ($innerQuery) use ($user) {
                    $innerQuery->where('users.id', $user->id);
                })->orWhereHas('group', function ($innerQuery) use ($user) {
                    $innerQuery->whereHas('users', function ($innerInnerQuery) use ($user) {
                        $innerInnerQuery->where('users.id', $user->id);
                    });
                });
            })
            ->where('user_id', '<>', $user->id)
            ->when($thread, fn ($query) => $query->where('thread_id', $thread->id))
            ->count();
    }
}
