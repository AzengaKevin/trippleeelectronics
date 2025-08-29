import { router } from '@inertiajs/vue3';

export default function useBrands() {
    const deleteBrand = (brand) => {
        const confirmed = confirm(`Are you sure you want to delete the brand: ${brand.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.brands.destroy', [brand.id]));
        }
    };

    return {
        deleteBrand,
    };
}
