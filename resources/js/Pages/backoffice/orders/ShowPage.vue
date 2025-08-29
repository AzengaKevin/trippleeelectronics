<script setup>
import OrderPaymentsComponent from '@/components/OrderPaymentsComponent.vue';
import useDate from '@/composables/useDate';
import useOrders from '@/composables/useOrders';
import usePrice from '@/composables/usePrice';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    order: {
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
        title: 'Orders',
        href: route('backoffice.orders.index'),
    },
    {
        title: `Order #${props.order.reference}`,
        href: null,
    },
];

const { deleteOrder, markComplete } = useOrders();
const { showFeedbackSwal } = useSwal();
const { formatPrice } = usePrice();
const { formatDate } = useDate();

const orderPaymentsDialog = ref(null);

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

const orderTotal = computed(() => props.order.items.map((i) => i.sub_total).reduce((total, num) => total + num, 0));

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
        <title>Order Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Order Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        v-if="auth.permissions.includes('update-orders')"
                        :href="route('backoffice.orders.edit', [order.id])"
                        class="btn btn-sm btn-info btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="edit" />
                        Edit
                    </Link>
                    <button
                        v-if="auth.permissions.includes('delete-orders')"
                        type="button"
                        @click="deleteOrder(order)"
                        class="btn btn-sm btn-error btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="trash-alt" />
                        Delete
                    </button>
                    <div>
                        <button type="button" @click="openOrderPaymentsDialog" class="btn btn-sm btn-primary btn-outline w-full rounded-full">
                            <font-awesome-icon icon="credit-card" />
                            Payments
                        </button>
                    </div>
                </div>
                <div class="flex-wrap-gap-3 flex items-center">
                    <a target="_blank" :href="route('backoffice.orders.invoice', [order.id])" class="btn btn-sm btn-primary btn-outline rounded-full">
                        <font-awesome-icon icon="file-invoice" />
                        Invoice
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-12 items-start gap-2">
                <div class="col-span-12 md:col-span-6">
                    <div class="card bg-blue-100 shadow">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto">
                                <table class="table-sm table w-full">
                                    <tbody>
                                        <tr>
                                            <th>Author</th>
                                            <td>{{ order.author?.name ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>ID</th>
                                            <td>{{ order.id }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Reference</th>
                                            <td>{{ order.reference ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>{{ order.order_status }}</td>
                                            <td>
                                                <button
                                                    v-if="order.can_mark_completed"
                                                    type="button"
                                                    @click="markComplete(order)"
                                                    class="btn btn-sm btn-success btn-outline"
                                                >
                                                    Mark Complete
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Payment Status</th>
                                            <td>{{ order.payment_status ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Filfulment Status</th>
                                            <td>{{ order.fulfillment_status ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Shipping Status</th>
                                            <td>{{ order.shipping_status ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Refund Status</th>
                                            <td>{{ order.refund_status ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Channel</th>
                                            <td>{{ order.channel ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Refferal Code</th>
                                            <td>{{ order.refferal_code ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Customer</th>
                                            <td>{{ order?.customer?.name ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>User</th>
                                            <td>{{ order?.user?.name ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Store</th>
                                            <td>{{ order?.store?.name ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Amount</th>
                                            <td>{{ formatPrice(order.amount) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Shipping Amount</th>
                                            <td>{{ order.shipping_amount ? formatPrice(order.shipping_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Tax Amount</th>
                                            <td>{{ order.tax_amount ? formatPrice(order.tax_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Discount Amount</th>
                                            <td>{{ order.discount_amount ? formatPrice(order.discount_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount</th>
                                            <td>{{ order.total_amount ? formatPrice(order.total_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Tendered Amount</th>
                                            <td>{{ order.tendered_amount ? formatPrice(order.tendered_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Balance Amount</th>
                                            <td>{{ order.balance_amount ? formatPrice(order.balance_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Notes</th>
                                            <td>{{ order.notes ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ formatDate(order.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ formatDate(order.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 md:sticky md:top-0 md:col-span-6">
                    <div class="card bg-blue-200 shadow">
                        <div class="card-body space-y-4">
                            <h2 class="text-xl font-semibold">Order Items</h2>
                            <div class="overflow-x-auto">
                                <table class="table-sm table w-full">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Tax Rate</th>
                                            <th>Discount Rate</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in order.items" :key="item.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ item.item?.name ?? '-' }}</td>
                                            <td>{{ item.quantity ?? '-' }}</td>
                                            <td>{{ item.tax_rate ?? '-' }}</td>
                                            <td>{{ item.discount_rate ?? '-' }}</td>
                                            <td>{{ item.final_price_per_item ? formatPrice(item.final_price_per_item) : '-' }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>#</td>
                                            <td colspan="4">Total</td>
                                            <td>{{ formatPrice(orderTotal) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <teleport to="body">
            <dialog ref="orderPaymentsDialog" class="modal">
                <order-payments-component :order="order" :payment-methods="paymentMethods" @close-dialog="closeOrderPaymentsDialog" />
            </dialog>
        </teleport>
    </BackofficeLayout>
</template>
