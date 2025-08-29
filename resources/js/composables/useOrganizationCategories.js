import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
export default function useOrganizationCategories() {
    const deleteOrganizationCategory = (category) => {
        const confirmed = confirm(`Are you sure you want to delete the organization category: ${category.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.organization-categories.destroy', [category.id]));
        }
    };

    return {
        deleteOrganizationCategory,
    };
}
