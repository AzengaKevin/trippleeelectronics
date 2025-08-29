<script setup>
import PurchasePaymentsComponent from '@/components/PurchasePaymentsComponent.vue';
import useDate from '@/composables/useDate';
import usePrice from '@/composables/usePrice';
import usePurchases from '@/composables/usePurchases';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    purchase: {
        type: Object,
        required: true,
    },
    paymentMethods: {
        type: Array,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Purchases',
        href: route('backoffice.purchases.index'),
    },
    {
        title: `Purchase #${props.purchase.reference}`,
        href: null,
    },
];

const { deletePurchase } = usePurchases();
const { formatPrice } = usePrice();
const { formatDate } = useDate();
const { showFeedbackSwal } = useSwal();

const purchaseTotal = computed(() => props.purchase.items.map((i) => i.sub_total).reduce((total, num) => total + num, 0));

const purchasePaymentsDialog = ref(null);

const openPurchasePaymentsDialog = () => {
    if (purchasePaymentsDialog.value) {
        purchasePaymentsDialog.value.showModal();
    }
};

const closePurchasePaymentsDialog = () => {
    if (purchasePaymentsDialog.value) {
        purchasePaymentsDialog.value.close();
    }
};

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
    {
        immediate: true,
    },
);
</script>

<template>
    <Head>
        <title>Purchase Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Purchase Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('update-purchases')"
                    :href="route('backoffice.purchases.edit', [purchase.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions.includes('delete-purchases')"
                    type="button"
                    @click="deletePurchase(purchase)"
                    class="btn btn-sm btn-error btn-outline rounded-full"
                >
                    <font-awesome-icon icon="trash-alt" />
                    Delete
                </button>
                <button type="button" @click="openPurchasePaymentsDialog" class="btn btn-sm btn-primary btn-outline rounded-full">
                    <font-awesome-icon icon="credit-card" />
                    Payments
                </button>
            </div>

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="col-span-12 md:col-span-9">
                    <div class="card bg-blue-200 shadow">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto">
                                <table class="table-sm table w-full">
                                    <tbody>
                                        <tr>
                                            <th>ID</th>
                                            <td>{{ purchase.id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Reference</th>
                                            <td>{{ purchase.reference ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Store</th>
                                            <td>{{ purchase.store?.name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Supplier</th>
                                            <td>{{ purchase?.supplier?.name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Amount</th>
                                            <td>{{ purchase.amount ? formatPrice(purchase.amount) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Shipping Amount</th>
                                            <td>{{ purchase.shipping_amount ? formatPrice(purchase.shipping_amount) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount</th>
                                            <td>{{ purchase.total_amount ? formatPrice(purchase.total_amount) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ formatDate(purchase.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ formatDate(purchase.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 md:sticky md:top-0 md:col-span-3">
                    <div class="card bg-[#87ceeb] shadow">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto">
                                <table class="table-sm table w-full">
                                    <thead>
                                        <tr>
                                            <th colspan="5">Purchase Items</th>
                                        </tr>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in purchase.items">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ item.item?.name ?? '-' }}</td>
                                            <td>{{ item.quantity ?? '-' }}</td>
                                            <td class="text-end">{{ item.sub_total ? formatPrice(item.sub_total) : '-' }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <td></td>
                                        <td colspan="2">Total</td>
                                        <td class="text-end">{{ formatPrice(purchaseTotal) }}</td>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <teleport to="body">
            <dialog ref="purchasePaymentsDialog" class="modal">
                <purchase-payments-component :purchase="purchase" :payment-methods="paymentMethods" @close-dialog="closePurchasePaymentsDialog" />
            </dialog>
        </teleport>
    </BackofficeLayout>
</template>
