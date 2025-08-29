import { router } from '@inertiajs/vue3';

export default function useQuotations() {
    const deleteQuotation = (quotation) => {
        const confirmed = confirm(`Are you sure you want to delete the quotation: ${quotation.reference}?`);

        if (confirmed) {
            router.delete(route('backoffice.quotations.destroy', [quotation.id]));
        }
    };

    return {
        deleteQuotation,
    };
}
