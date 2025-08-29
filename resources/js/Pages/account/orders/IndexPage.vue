<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
    treeCategories: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    services: {
        type: Array,
        required: true,
    },
    orders: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    {
        title: 'Account',
        link: route('account.dashboard'),
    },
    {
        title: 'Orders',
        link: null,
    },
];

const { formatDate } = useDate();
</script>
<template>
    <Head>
        <title>Orders</title>
    </Head>
    <app-layout
        :auth="auth"
        :settings="settings"
        :tree-categories="treeCategories"
        :categories="categories"
        :services="services"
        :breadcrumbs="breadcrumbs"
        title="Orders"
    >
        <div class="space-y-4">
            <app-alert />

            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <div class="overflow-x-auto pb-48">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in orders.data" :key="order.id">
                                    <td>{{ order.reference }}</td>
                                    <td>{{ order.total_amount }}</td>
                                    <td>{{ order.order_status }}</td>
                                    <td>{{ formatDate(order.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                    <td>
                                        <div class="dropdown dropdown-end">
                                            <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                <font-awesome-icon icon="ellipsis-vertical" />
                                            </div>
                                            <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                <li>
                                                    <a target="_blank" :href="route('account.orders.receipt', [order.id])"> Receipt</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <app-pagination :resource="orders" />
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>
