import { router } from '@inertiajs/vue3';

export default function usePaymentMethods() {
    const deletePaymentMethod = (paymentMethod) => {
        const confirmed = confirm(`Are you sure you want to delete the payment method: ${paymentMethod.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.payment-methods.destroy', [paymentMethod.id]));
        }
    };

    return {
        deletePaymentMethod,
    };
}
