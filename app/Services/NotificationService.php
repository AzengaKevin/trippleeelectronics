<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function getByUser(
        User $user,
        ?bool $with_read = null,
        ?int $perPage = 72
    ) {
        $notificationQuery = $user->unreadNotifications();

        if (boolval($with_read)) {

            $notificationQuery = $user->notifications();
        }

        return is_null($perPage)
            ? $notificationQuery->get()
            : $notificationQuery->paginate(perPage: $perPage);
    }

    public function markNotificationsAsRead(array $notificationIds)
    {
        DB::transaction(function () use ($notificationIds) {

            DatabaseNotification::query()->whereIn('id', $notificationIds)->update([
                'read_at' => now(),
            ]);
        });
    }

    public function deleteNotifications(array $notificationIds)
    {
        DB::transaction(function () use ($notificationIds) {

            DatabaseNotification::query()->whereIn('id', $notificationIds)->delete();
        });
    }
}
