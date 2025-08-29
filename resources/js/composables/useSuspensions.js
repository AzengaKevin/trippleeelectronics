import { router } from '@inertiajs/vue3';

export default function useSuspensions() {
    const deleteSuspension = (suspension) => {
        const confirmed = confirm(`Are you sure you want to delete the suspension for: ${suspension.employee.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.suspensions.destroy', [suspension.id]));
        }
    };

    return {
        deleteSuspension,
    };
}
