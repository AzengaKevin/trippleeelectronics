import { router } from '@inertiajs/vue3';

export default function useStores() {
    const deleteStore = (store) => {
        const confirmed = confirm(`Are you sure you want to delete the store: ${store.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.stores.destroy', [store.id]));
        }
    };

    return {
        deleteStore,
    };
}
