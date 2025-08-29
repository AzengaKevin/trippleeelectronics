<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useStockLevels from '@/composables/useStockLevels';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { Download, Plus, Upload } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    stockLevels: {
        type: Object,
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
        title: 'Stock Levels',
        href: null,
    },
];

const { deleteStockLevel } = useStockLevels();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.stock-levels.index'),
            { ...newFilters },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 500),
    {
        deep: true,
    },
);

const resetFilters = () => {
    filters.query = undefined;
    filters.store = undefined;

    router.get(route('backoffice.stock-levels.index'), filters, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head>
        <title>Stock Levels</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Stock Levels">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-stock-levels')"
                    :href="route('backoffice.stock-levels.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('import-stock-levels')"
                    :href="route('backoffice.stock-levels.import')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span>Import</span>
                </Link>
            </div>

            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-[#87ceeb]">
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Name</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>
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
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.stock-levels.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    Reset
                                </Link>

                                <a
                                    v-if="auth.permissions.includes('export-stock-levels', filters)"
                                    :href="route('backoffice.stock-levels.export')"
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
                    <div class="card bg-base-100">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Store</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="props.stockLevels.data.length === 0">
                                            <tr>
                                                <td colspan="7" class="text-center">No stock levels found.</td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr
                                                v-for="(stock, index) in props.stockLevels.data"
                                                :key="stock.id"
                                                :class="[
                                                    stock.quantity < 0
                                                        ? 'border-b border-red-500'
                                                        : stock.quantity < 20
                                                          ? 'border-b border-yellow-500'
                                                          : stock.quantity < 100
                                                            ? 'border-b border-blue-500'
                                                            : 'border-b border-green-500',
                                                ]"
                                            >
                                                <td>{{ props.stockLevels.from + index }}</td>
                                                <td>{{ stock.store?.name ?? '-' }}</td>
                                                <td>{{ stock.stockable?.name }}</td>
                                                <td>{{ stock.quantity }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.stock-levels.show', [stock.id])"> Details</Link>
                                                            </li>
                                                            <li>
                                                                <Link :href="route('backoffice.stock-levels.edit', [stock.id])"> Edit</Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deleteStockLevel(stock)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <app-pagination :resource="stockLevels" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
