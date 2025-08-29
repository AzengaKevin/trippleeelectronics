import { router } from '@inertiajs/vue3';

export default function useNotifications() {
    const deleteNotification = (notification) => {
        const confirmed = confirm(`Are you sure you want to delete the notification: #${notification.id}?`);

        if (confirmed) {
            router.delete(route('backoffice.notifications.destroy', [notification.id]));
        }
    };

    const markAsRead = (notification) => {
        const confirmed = confirm(`Are you sure you want to mark the notification: #${notification.id} as read?`);
        if (confirmed) {
            router.patch(route('backoffice.notifications.mark-as-read', [notification.id]));
        }
    };

    return {
        deleteNotification,
        markAsRead,
    };
}
