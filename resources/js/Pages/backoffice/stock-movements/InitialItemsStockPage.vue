<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import ImportInitialItemsStockDialog from '@/Pages/backoffice/stock-movements/ImportInitialItemsStockDialog.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { Download } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    items: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    brands: {
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
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Stock Movements',
        href: route('backoffice.stock-movements.index'),
    },
    {
        title: 'Initial Items Stock',
        href: null,
    },
];

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.stock-movements.initial-items-stock'), newFilters, { preserveState: true, replace: true });
    }, 300),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Initial Items Stock</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Initial Items Stock">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <button class="btn btn-primary btn-outline rounded-full" onclick="importInitialItemsStockDialog.showModal()">
                    <font-awesome-icon icon="file-import" />
                    <span>Import Initial Stock</span>
                </button>
            </div>

            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-8">
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>SKU</th>
                                            <th>Item</th>
                                            <th>Store</th>
                                            <th>Initial Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in props.items.data" :key="item.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ item.sku ?? '-' }}</td>
                                            <td>{{ item.name ?? '-' }}</td>
                                            <td>{{ item.store_name }}</td>
                                            <td>{{ item.quantity ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <app-pagination :resource="items" />
                        </div>
                    </div>
                </div>
                <div class="top-0 order-first col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <h2 class="text-lg font-semibold">Filters</h2>
                            <div class="space-y-2">
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
                                    <span class="label-text">Category</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.category">
                                    <option :value="undefined">All</option>
                                    <option v-for="category in props.categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Brand</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.brand">
                                    <option :value="undefined">All</option>
                                    <option v-for="brand in props.brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                                </select>
                            </div>

                            <hr />

                            <div class="flex flex-wrap gap-3">
                                <Link
                                    :href="route('backoffice.stock-movements.initial-items-stock')"
                                    class="btn btn-sm btn-primary btn-outline rounded-full"
                                >
                                    <font-awesome-icon icon="times" />
                                    <span>Clear</span>
                                </Link>

                                <a
                                    v-if="auth.permissions.includes('export-stock-movements')"
                                    :href="route('backoffice.stock-movements.initial-items-stock.export', filters)"
                                    class="btn btn-sm btn-outline btn-primary rounded-full"
                                >
                                    <Download class="h-4 w-4" />
                                    <span>Export</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <teleport to="body">
            <ImportInitialItemsStockDialog />
        </teleport>
    </BackofficeLayout>
</template>
