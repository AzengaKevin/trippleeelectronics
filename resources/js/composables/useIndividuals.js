import { router } from '@inertiajs/vue3';

export default function useIndividuals() {
    const deleteIndividual = (individual) => {
        const confirmed = confirm(`Are you sure you want to delete the individual: ${individual.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.individuals.destroy', [individual.id]));
        }
    };

    return {
        deleteIndividual,
    };
}
