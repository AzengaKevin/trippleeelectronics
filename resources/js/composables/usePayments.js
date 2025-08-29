import { router } from '@inertiajs/vue3';

export default function usePayments() {
    const deletePayment = (payment) => {
        const confirmed = confirm(`Are you sure you want to delete the payment: ${payment.id}?`);

        if (confirmed) {
            router.delete(route('backoffice.payments.destroy', [payment.id]));
        }
    };

    return {
        deletePayment,
    };
}
