import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

export default function useOrders() {
    const orders = ref([]);

    const fetchOrders = async ({ query = null, store = null, status = null, withOutstandingAmount = null, from = null, to = null } = {}) => {
        try {
            const response = await axios.get(route('api.orders.index'), {
                params: {
                    query,
                    store,
                    status,
                    withOutstandingAmount,
                    from,
                    to,
                },
            });

            orders.value = response.data.data;
        } catch (error) {
            console.log(error);
        }
    };

    const deleteOrder = (order) => {
        const confirmed = confirm(`Are you sure you want to delete the order: ${order.reference}?`);

        if (confirmed) {
            router.delete(route('backoffice.orders.destroy', [order.id]));
        }
    };

    const markComplete = (order) => {
        const confirmed = confirm(`Are you sure you want to mark the order: ${order.reference} as complete?`);

        if (confirmed) {
            router.patch(route('backoffice.orders.mark-complete', [order.id]));
        }
    };

    const printReceipt = (order) => {
        const win = window.open(route('backoffice.orders.receipt', [order.id]), '_blank');
        win.focus();
        win.onafterprint = function () {
            win.close();
        };
        setTimeout(() => win.print(), 500);
    };

    const partiallyUpdateOrder = (order, payload) => {
        router.patch(route('backoffice.orders.update', [order.id]), payload, {
            preserveState: false,
        });
    };

    return {
        orders,
        fetchOrders,
        deleteOrder,
        markComplete,
        printReceipt,
        partiallyUpdateOrder,
    };
}
