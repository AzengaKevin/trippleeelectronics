import { router } from '@inertiajs/vue3';

export default function usePurchases() {
    const deletePurchase = (purchase) => {
        const confirmed = confirm(`Are you sure you want to delete the purchase: ${purchase.reference}?`);

        if (confirmed) {
            router.delete(route('backoffice.purchases.destroy', [purchase.id]));
        }
    };

    return {
        deletePurchase,
    };
}
