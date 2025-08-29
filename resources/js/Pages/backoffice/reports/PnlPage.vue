<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useNumber from '@/composables/useNumber';
import usePrice from '@/composables/usePrice';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    stores: {
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
    items: {
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
        title: 'Reports',
        href: route('backoffice.reports.index'),
    },
    {
        title: 'P & L',
        href: null,
    },
];

const filters = reactive({ ...props.params });

const { formatPrice } = usePrice();
const { formatPercent } = useNumber();

watch(
    filters,
    (newFilters) => {
        router.get(
            route('backoffice.reports.pnl'),
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
        <title>P & L</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="P & L">
        <div class="space-y-4">
            <AppAlert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-6">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
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
                            <div v-if="auth.user.roles.map((r) => r.name).includes('admin')" class="space-y-2">
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
                            <template v-if="auth.user.roles.map((r) => r.name).includes('staff')">
                                <label class="flex items-center gap-3">
                                    <input type="checkbox" v-model="filters.mine" :value="true" class="checkbox checkbox-primary" />
                                    <span>Mine Only</span>
                                </label>
                            </template>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.reports.pnl')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    Reset
                                </Link>
                                <a
                                    :href="route('backoffice.reports.pnl.print', filters)"
                                    target="_blank"
                                    class="btn btn-sm btn-primary btn-outline rounded-full"
                                >
                                    <font-awesome-icon icon="print" />
                                    Print
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Qty</th>
                                            <th class="text-center">Cost</th>
                                            <th class="text-center">Sales</th>
                                            <th class="text-center">Net Profit</th>
                                            <th class="text-center">Profit Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in props.items.data" :key="item.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ item.name }}</td>
                                            <td>{{ item.quantity }}</td>
                                            <td class="text-end">{{ formatPrice(item.total_cost) }}</td>
                                            <td class="text-end">{{ formatPrice(item.total_sales) }}</td>
                                            <td class="text-end">{{ formatPrice(item.net_profit) }}</td>
                                            <td class="text-end">{{ formatPercent(item.profit_margin) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <app-pagination :resource="items" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
