import { router } from '@inertiajs/vue3';

export default function useProperties() {
    const deleteProperty = (property) => {
        if (confirm(`Are you sure you want to delete the property "${property.name}"?`)) {
            router.delete(route('backoffice.properties.destroy', property.id));
        }
    };

    return {
        deleteProperty,
    };
}
