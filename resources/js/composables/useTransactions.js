import { router } from '@inertiajs/vue3';

export default function useTransactions() {
    const deleteTransaction = (transaction) => {
        const confirmed = confirm(`Are you sure you want to delete the transaction: ${transaction.local_reference}?`);

        if (confirmed) {
            router.delete(route('backoffice.transactions.destroy', [transaction.id]));
        }
    };

    return {
        deleteTransaction,
    };
}
