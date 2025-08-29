<?php

use App\Models\Thread;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('threads.{threadId}', function ($user, $threadId) {

    $thread = Thread::query()->with(['group.users'])->findOrFail($threadId);

    if ($thread && $thread->group) {

        return $thread->group->users->contains($user);
    }

    return $thread->users->contains($user);
});
