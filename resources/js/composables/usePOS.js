import { ref } from 'vue';

export default function usePOS() {
    const updateStoreDialog = ref(null);

    const orderPaymentsDialog = ref(null);

    const orderCustomerDialog = ref(null);

    const payLaterOrdersDialog = ref(null);

    const openUpdateStoreDialog = () => {
        if (updateStoreDialog.value) {
            updateStoreDialog.value.showModal();
        }
    };

    const closeUpdateStoreDialog = () => {
        if (updateStoreDialog.value) {
            updateStoreDialog.value.close();
        }
    };

    const openPayLaterOrdersDialog = () => {
        if (payLaterOrdersDialog.value) {
            payLaterOrdersDialog.value.showModal();
        }
    };

    const closePayLaterOrdersDialog = () => {
        if (payLaterOrdersDialog.value) {
            payLaterOrdersDialog.value.close();
        }
    };

    const openOrderPaymentsDialog = () => {
        if (orderPaymentsDialog.value) {
            orderPaymentsDialog.value.showModal();
        }
    };

    const closeOrderPaymentsDialog = () => {
        if (orderPaymentsDialog.value) {
            orderPaymentsDialog.value.close();
        }
    };

    const openOrderCustomerDialog = () => {
        if (orderCustomerDialog.value) {
            orderCustomerDialog.value.showModal();
        }
    };

    const closeOrderCustomerDialog = () => {
        if (orderCustomerDialog.value) {
            orderCustomerDialog.value.close();
        }
    };

    return {
        updateStoreDialog,
        orderPaymentsDialog,
        orderCustomerDialog,
        payLaterOrdersDialog,
        closeUpdateStoreDialog,
        openUpdateStoreDialog,
        openOrderPaymentsDialog,
        closeOrderPaymentsDialog,
        openOrderCustomerDialog,
        closeOrderCustomerDialog,
        openPayLaterOrdersDialog,
        closePayLaterOrdersDialog,
    };
}
