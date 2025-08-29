import { router } from '@inertiajs/vue3';

export default function useCarousels() {
    const deleteCarousel = (carousel) => {
        const confirmed = confirm(`Are you sure you want to delete the carousel: ${carousel.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.carousels.destroy', [carousel.id]));
        }
    };

    return {
        deleteCarousel,
    };
}
