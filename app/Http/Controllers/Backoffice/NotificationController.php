<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;

class NotificationController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {

        $params = $request->only('with_read');

        $filters['with_read'] = $request->boolean('with_read');

        /** @var User $user */
        $user = $request->user();

        $notifications = $this->notificationService->getByUser($user, ...$filters);

        return Inertia::render('backoffice/notifications/IndexPage', compact('notifications', 'params'));
    }

    public function markAsRead(DatabaseNotification $notification)
    {

        try {

            $notification->markAsRead();

            return $this->sendSuccessRedirect('Notification marked as read', url()->previous());
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Marking notification as read failed', $throwable);
        }
    }

    public function destroy(DatabaseNotification $notification)
    {

        try {

            $notification->delete();

            return $this->sendSuccessRedirect('Notification deleted', url()->previous());
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Deleting notification failed', $throwable);
        }
    }

    public function bulkMarkAsRead(Request $request)
    {

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['uuid'],
        ]);

        try {

            $this->notificationService->markNotificationsAsRead($ids = data_get($data, 'ids'));

            $idsCount = count($ids);

            return $this->sendSuccessRedirect("You've updated marked {$idsCount} notifications read", url()->previous());
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Marking notifications as read failed', $throwable);
        }
    }

    public function bulkDelete(Request $request)
    {

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['uuid'],
        ]);

        try {

            $this->notificationService->deleteNotifications($ids = data_get($data, 'ids'));

            $idsCount = count($ids);

            return $this->sendSuccessRedirect("You've deleted {$idsCount} notifications", url()->previous());
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Deleting notifications failed', $throwable);
        }
    }
}
