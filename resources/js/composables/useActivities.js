import { router } from '@inertiajs/vue3';

export default function useActivities() {
    const deleteActivity = (activity) => {
        if (confirm('Are you sure you want to delete this activity?')) {
            router.delete(route('backoffice.activities.destroy', [activity.id]));
        }
    };

    return {
        deleteActivity,
    };
}
