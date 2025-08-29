<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import useOrders from '@/composables/useOrders';
import usePrice from '@/composables/usePrice';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import debounce from 'lodash/debounce';

import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Plus, Upload } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    orders: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    stores: {
        type: Array,
        required: true,
    },
    params: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Orders', href: null },
];

const { deleteOrder } = useOrders();

const { formatPrice } = usePrice();

const { showFeedbackSwal } = useSwal();

const { formatDate } = useDate();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.orders.index'), { ...newFilters }, { preserveState: true, replace: true });
    }, 500),
    { deep: true },
);

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
        <title>Orders</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Orders">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-orders')"
                    :href="route('backoffice.orders.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('import-orders')"
                    :href="route('backoffice.orders.import')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span>Import</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('browse-orders')"
                    :href="route('backoffice.orders.reports')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <font-awesome-icon icon="chart-line" />
                    <span>Reports</span>
                </Link>
            </div>

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-[#87ceeb]">
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>

                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">From</span>
                                </label>
                                <input type="date" class="input input-bordered w-full" v-model="filters.from" placeholder="From" />
                            </div>

                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">To</span>
                                </label>
                                <input type="date" class="input input-bordered w-full" v-model="filters.to" placeholder="To" />
                            </div>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Name</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>
                            <div v-if="auth.user.roles.map((r) => r.name).includes('admin')" class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Store</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.store">
                                    <option :value="undefined">All</option>
                                    <template v-for="store in props.stores" :key="store.id">
                                        <option :value="store.id">{{ store.name }}</option>
                                    </template>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Status</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.status">
                                    <option :value="undefined">All</option>
                                    <template v-for="status in props.statuses" :key="status.value">
                                        <option :value="status.value">{{ status.label }}</option>
                                    </template>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-sm checkbox-primary"
                                        v-model="filters.withOutstandingAmount"
                                        :value="true"
                                    />
                                    <span class="label-text">With Outstanding Amount</span>
                                </label>
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.orders.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    Reset
                                </Link>

                                <a
                                    v-if="auth.permissions.includes('export-orders')"
                                    :href="route('backoffice.orders.export')"
                                    class="btn btn-sm btn-outline btn-primary rounded-full"
                                >
                                    <Download class="h-4 w-4" />
                                    <span>Export</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-blue-200">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-xs table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Reference</th>
                                            <th>Time</th>
                                            <th>Store</th>
                                            <th>Author</th>
                                            <th>Customer</th>
                                            <th>Total</th>
                                            <th>Outstanding</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="props.orders.data.length">
                                            <tr v-for="(order, index) in props.orders.data" :key="order.id">
                                                <td>{{ index + props.orders.from }}</td>
                                                <td>
                                                    <Link
                                                        :href="route('backoffice.orders.show', [order.id])"
                                                        class="text-primary font-bold hover:underline"
                                                    >
                                                        {{ order.reference }}
                                                    </Link>
                                                </td>
                                                <td>{{ formatDate(order.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                                <td>{{ order.store?.name ?? '-' }}</td>
                                                <td>{{ order.author?.name ?? '-' }}</td>
                                                <td>{{ order.customer?.name ?? '-' }}</td>
                                                <td>{{ formatPrice(order.total_amount) }}</td>
                                                <td>{{ formatPrice(order.total_amount - order.paid_amount) }}</td>
                                                <td>
                                                    {{ order.order_status }}
                                                </td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.orders.show', [order.id])"> Details</Link>
                                                            </li>
                                                            <li>
                                                                <Link :href="route('backoffice.orders.edit', [order.id])"> Edit </Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deleteOrder(order)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="10" class="text-center">No orders found.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="props.orders" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
