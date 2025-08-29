<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;

class ThreadService
{
    public function __construct() {}

    public function get(
        ?string $query = null,
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
        ?User $user = null,
        ?string $orderBy = 'last_updated_at',
        ?string $orderDir = 'desc',
    ) {

        $threadQuery = Thread::query();

        $threadQuery->select('threads.*');

        $threadQuery->addSelect([
            'unread_messages_count' => Message::query()
                ->selectRaw('count(*)')
                ->whereColumn('messages.thread_id', 'threads.id')
                ->whereDoesntHave('userReads', fn ($query) => $query->where('user_id', $user->id))
                ->where('messages.user_id', '<>', $user->id),
        ]);

        $threadQuery->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('name', 'like', "%{$query}%");
        });

        $threadQuery->when($user, function ($queryBuilder) use ($user) {
            $queryBuilder->whereHas('users', fn ($query) => $query->where('users.id', $user->id))
                ->orWhereHas('group', fn ($query) => $query->whereHas('users', fn ($query) => $query->where('users.id', $user->id)));
        });

        $threadQuery->when($limit, function ($queryBuilder) use ($limit) {
            $queryBuilder->limit($limit);
        });

        $threadQuery->when($with, function ($queryBuilder) use ($with) {
            $queryBuilder->with($with);
        });

        $threadQuery->when($withCount, function ($queryBuilder) use ($withCount) {
            $queryBuilder->withCount($withCount);
        });

        $threadQuery->orderBy($orderBy, $orderDir);

        return is_null($perPage)
            ? $threadQuery->get()
            : $threadQuery->paginate($perPage);
    }
}
