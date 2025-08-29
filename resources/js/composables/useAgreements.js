import { router } from '@inertiajs/vue3';

export default function useAgreements() {
    const deleteAgreement = (agreement) => {
        const confirmed = confirm(`Are you sure you want to delete the agreement: # ${agreement.id}?`);

        if (confirmed) {
            router.delete(route('backoffice.agreements.destroy', [agreement.id]));
        }
    };

    return {
        deleteAgreement,
    };
}
