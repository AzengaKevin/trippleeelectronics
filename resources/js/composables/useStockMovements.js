import { router } from '@inertiajs/vue3';

export default function useStockMovements() {
    const deleteStockMovement = (movement) => {
        const confirmed = confirm(`Are you sure you want to delete the stock movement: ${movement.id}?`);

        if (confirmed) {
            router.delete(route('backoffice.stock-movements.destroy', [movement.id]));
        }
    };

    return {
        deleteStockMovement,
    };
}
