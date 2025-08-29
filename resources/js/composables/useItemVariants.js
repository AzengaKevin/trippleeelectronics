import { router } from '@inertiajs/vue3';

export default function useItemVariants() {
    const deleteItemVariant = (variant) => {
        const confirmed = confirm(`Are you sure you want to delete the variant: ${variant.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.item-variants.destroy', [variant.id]));
        }
    };

    return {
        deleteItemVariant,
    };
}
