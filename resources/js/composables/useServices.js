import { router } from '@inertiajs/vue3';

export default function useServices() {
    const deleteService = (service) => {
        const confirmed = confirm(`Are you sure you want to delete the service: ${service.title}?`);

        if (confirmed) {
            router.delete(route('backoffice.services.destroy', [service.id]));
        }
    };

    return {
        deleteService,
    };
}
