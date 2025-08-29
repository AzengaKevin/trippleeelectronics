<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useOrders from '@/composables/useOrders';
import usePayments from '@/composables/usePayments';
import usePrice from '@/composables/usePrice';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

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
        required: false,
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
        title: `#${props.order.reference}`,
        href: route('backoffice.orders.show', props.order.id),
    },
    {
        title: 'Payments',
        href: null,
    },
];

const { deletePayment } = usePayments();

const { deleteOrder } = useOrders();

const { formatPrice } = usePrice();

const balance = computed(
    () => props.order.total_amount - props.order.payments.filter((p) => p.status === 'paid').reduce((acc, p) => acc + p.amount, 0),
);
</script>
<template>
    <Head>
        <title>Order Payments</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Order Payments">
        <div class="space-y-4">
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
            </div>

            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 space-y-4 md:sticky md:order-last md:col-span-3">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-bold">Order Summary</h2>
                                </div>
                            </div>
                            <hr class="border-slate-300" />

                            <div class="space-y-2">
                                <label class="label"><span class="label-text">Order Amount</span></label>
                                <div>
                                    <span class="text-xl font-bold">{{ formatPrice(order.amount) }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="label"><span class="label-text">Shipping Amount</span></label>
                                <div>
                                    <span class="text-xl font-bold">{{ formatPrice(order.shipping_amount) }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="label"><span class="label-text">Total Amount</span></label>
                                <div>
                                    <span class="text-xl font-bold">{{ formatPrice(order.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-9">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Author</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="order.payments.length">
                                            <tr v-for="(payment, index) in order.payments" :key="payment.id">
                                                <td>{{ 1 + index }}</td>
                                                <td>{{ payment.amount }}</td>
                                                <td>{{ payment.payment_method?.name ?? '-' }}</td>
                                                <td>{{ payment.author?.name ?? '-' }}</td>
                                                <td>{{ payment.status ?? '-' }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li><a href="#" role="button" @click="deletePayment(payment)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="6" class="text-center">No payments found</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
