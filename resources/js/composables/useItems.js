import { router } from '@inertiajs/vue3';

export default function useItems() {
    const deleteItem = (item) => {
        const confirmed = confirm(`Are you sure you want to delete the item: ${item.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.items.destroy', [item.id]));
        }
    };

    return {
        deleteItem,
    };
}
