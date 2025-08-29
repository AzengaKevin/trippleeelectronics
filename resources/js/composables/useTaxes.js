import { router } from '@inertiajs/vue3';

export default function useTaxes() {
    const deleteTax = (tax) => {
        if (confirm('Are you sure you want to delete this tax?')) {
            router.delete(route('backoffice.taxes.destroy', tax.id));
        }
    };

    return {
        deleteTax,
    };
}
