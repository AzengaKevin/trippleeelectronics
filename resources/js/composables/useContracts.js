import { router } from '@inertiajs/vue3';

export default function useContracts() {
    const deleteContract = (contract) => {
        const confirmed = confirm(`Are you sure you want to delete the contract for: ${contract.employee.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.contracts.destroy', [contract.id]));
        }
    };

    return {
        deleteContract,
    };
}
