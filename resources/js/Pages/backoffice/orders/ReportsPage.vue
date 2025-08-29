<script setup>
import AppAlert from '@/components/AppAlert.vue';
import BarChartComponent from '@/components/BarChartComponent.vue';
import PieChartComponent from '@/components/PieChartComponent.vue';
import usePrice from '@/composables/usePrice';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    statistics: {
        type: Object,
        required: true,
    },
    stores: {
        type: Array,
        required: true,
    },
    orderStatuses: {
        type: Array,
        required: true,
    },
    authors: {
        type: Array,
        default: () => [],
    },
    params: {
        type: Object,
        default: () => ({}),
    },
    feedback: {
        type: Object,
        default: null,
    },
    auth: {
        type: Object,
        required: true,
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
        title: 'Reports',
        href: null,
    },
];

const summary = ref({
    ordersTrend: 12,
    avgOrderTrend: -5,
});

const pieChartData = ref({
    labels: props.statistics.order_status_distribution.map((item) => item.label),
    datasets: [
        {
            data: props.statistics.order_status_distribution.map((item) => item.count),
            backgroundColor: props.statistics.order_status_distribution.map((item) => item.color),
            borderWidth: 0,
        },
    ],
});

const barChartData = ref(
    props.statistics.last_seven_days_orders_count?.map((item) => ({
        label: item.date,
        value: item.count,
    })),
);

const pieOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right' },
    },
});

const barOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { beginAtZero: true },
    },
});

const filters = reactive({ ...props.params });

const { formatPrice } = usePrice();

watch(
    filters,
    (newFilters) => {
        router.get(
            route('backoffice.orders.reports'),
            {
                ...newFilters,
            },
            {
                replace: true,
            },
        );
    },
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Orders Reports</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Orders Reports">
        <div class="space-y-4">
            <AppAlert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-6">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-[#87ceeb]">
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Date Range</span>
                                </label>
                                <input type="date" class="input input-bordered w-full" v-model="filters.from" placeholder="From" />
                                <input type="date" class="input input-bordered w-full" v-model="filters.to" placeholder="To" />
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
                                    <template v-for="status in orderStatuses" :key="status.value">
                                        <option :value="status.value">{{ status.label }}</option>
                                    </template>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Author</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.author">
                                    <option :value="undefined">All</option>
                                    <template v-for="author in props.authors" :key="author.id">
                                        <option :value="author.id">{{ author.name }}</option>
                                    </template>
                                </select>
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.orders.reports')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    Reset
                                </Link>

                                <a
                                    target="_blank"
                                    v-if="auth.permissions.includes('export-orders')"
                                    :href="route('backoffice.orders.reports.detailed-report', { ...filters })"
                                    class="btn btn-sm btn-outline btn-primary rounded-full"
                                >
                                    <font-awesome-icon icon="print" />
                                    Detailed
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 grid grid-cols-1 gap-6 lg:col-span-9">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
                        <div class="card bg-base-100 shadow">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <h2 class="card-title text-sm opacity-70">Total Orders</h2>
                                <p class="text-3xl font-bold">{{ statistics.total_orders ? statistics.total_orders.toLocaleString() : '-' }}</p>
                            </div>
                        </div>

                        <div class="card bg-base-100 sadow">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <h2 class="card-title text-sm opacity-70">Total Sales</h2>
                                <p class="text-3xl font-bold">
                                    {{ statistics.total_sales ? formatPrice(statistics.total_sales) : '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="card bg-base-100 shadow">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <h2 class="card-title text-sm opacity-70">Largest Order</h2>
                                <p class="text-3xl font-bold">
                                    {{ statistics.largest_order ? formatPrice(statistics.largest_order.total_amount) : '-' }}
                                </p>
                                <div v-if="statistics.largest_order" class="badge badge-info gap-2">#{{ statistics.largest_order.reference }}</div>
                            </div>
                        </div>

                        <div class="card bg-base-100 shadow">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <h2 class="card-title text-sm opacity-70">Smallest Order</h2>
                                <p class="text-3xl font-bold">
                                    {{ statistics.smallest_order ? formatPrice(statistics.smallest_order.total_amount) : '-' }}
                                </p>
                                <div v-if="statistics.smallest_order" class="badge badge-warning gap-2">
                                    #{{ statistics.smallest_order.reference }}
                                </div>
                            </div>
                        </div>

                        <div class="card bg-base-100 shadow">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <h2 class="card-title text-sm opacity-70">Avg. Order Value</h2>
                                <p class="text-3xl font-bold">
                                    {{ statistics.average_order_value ? formatPrice(statistics.average_order_value) : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Pie Chart -->
                        <div class="card bg-base-100 shadow">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <h2 class="card-title">Order Status</h2>
                                <div class="h-96 w-full">
                                    <pie-chart-component :data="pieChartData" :options="pieOptions" />
                                </div>
                            </div>
                        </div>

                        <!-- Bar Chart -->
                        <div class="card bg-base-100 shadow">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <h2 class="card-title">Last 7 Days' Orders</h2>
                                <div class="h-96 w-full">
                                    <bar-chart-component :data="barChartData" :options="barOptions" title="Bar Chart" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
