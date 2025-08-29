import { router } from '@inertiajs/vue3';

export default function useResources() {
    const deleteResource = (resource) => {
        const confirmed = confirm(`Are you sure you want to delete the resource: ${resource.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.resources.destroy', [resource.id]));
        }
    };

    return {
        deleteResource,
    };
}
