import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
export default function useItemCategories() {
    const deleteItemCategory = (category) => {
        const confirmed = confirm(`Are you sure you want to delete the item category: ${category.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.item-categories.destroy', [category.id]));
        }
    };

    return {
        deleteItemCategory,
    };
}
