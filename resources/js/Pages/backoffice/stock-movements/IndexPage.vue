<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useStockMovements from '@/composables/useStockMovements';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import StockTransferDialog from '@/Pages/backoffice/stock-movements/StockTransferDialog.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { Download, Plus, Upload } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    stockMovements: {
        type: Object,
        required: true,
    },
    stores: {
        type: Array,
        required: true,
    },
    types: {
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
    { title: 'Stock Movements', href: null },
];

const { deleteStockMovement } = useStockMovements();
const { showFeedbackSwal } = useSwal();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.stock-movements.index'),
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
        <title>Stock Movements</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Stock Movements">
        <div class="space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <Link
                    v-if="auth.permissions.includes('create-stock-movements')"
                    :href="route('backoffice.stock-movements.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <button
                    v-if="auth.permissions.includes('transfer-stock')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                    onclick="stockTransferDialog.showModal()"
                >
                    <font-awesome-icon icon="exchange-alt" />
                    <span>Transfer</span>
                </button>
                <Link
                    v-if="auth.permissions.includes('import-stock-movements')"
                    :href="route('backoffice.stock-movements.import')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span>Import</span>
                </Link>
            </div>

            <div class="grid grid-cols-12 items-start gap-2">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-[#87ceeb]">
                        <div class="card-body space-y-2">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-1">
                                <label class="label">
                                    <span class="label-text">Name</span>
                                </label>
                                <input type="text" class="input input-sm input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>
                            <div class="space-y-1">
                                <label class="label">
                                    <span class="label-text">Store</span>
                                </label>
                                <select class="select select-sm select-bordered w-full" v-model="filters.store">
                                    <option :value="undefined">All</option>
                                    <template v-for="store in props.stores" :key="store.id">
                                        <option :value="store.id">{{ store.name }}</option>
                                    </template>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="label">
                                    <span class="label-text">Type</span>
                                </label>
                                <select class="select select-sm select-bordered w-full" v-model="filters.type">
                                    <option :value="undefined">All</option>
                                    <template v-for="{ value, label } in props.types" :key="value">
                                        <option :value="value">{{ label }}</option>
                                    </template>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-sm checkbox-primary"
                                        v-model="filters.withoutStore"
                                        :value="true"
                                    />
                                    <span class="label-text">Without Store</span>
                                </label>
                            </div>
                            <div class="space-y-1">
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-sm checkbox-primary"
                                        v-model="filters.withoutStockable"
                                        :value="true"
                                    />
                                    <span class="label-text">Without Item</span>
                                </label>
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    :href="route('backoffice.stock-movements.index')"
                                    type="button"
                                    class="btn btn-xs btn-primary btn-outline rounded-full"
                                >
                                    <font-awesome-icon icon="times" />
                                    Reset
                                </Link>

                                <a
                                    v-if="auth.permissions.includes('export-stock-movements')"
                                    :href="route('backoffice.stock-movements.export', filters)"
                                    class="btn btn-xs btn-outline btn-primary rounded-full"
                                >
                                    <Download class="h-4 w-4" />
                                    <span>Export</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-sm table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Store</th>
                                            <th>Item</th>
                                            <th>Type</th>
                                            <th>Action</th>
                                            <th>Quantity</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="props.stockMovements.data.length">
                                            <tr
                                                v-for="(movement, index) in props.stockMovements.data"
                                                :key="movement.id"
                                                :class="[movement.quantity > 0 ? 'border-b border-blue-500' : 'border-b border-green-500']"
                                            >
                                                <td>{{ props.stockMovements.from + index }}</td>
                                                <td>{{ movement.store?.name ?? '-' }}</td>
                                                <td>{{ movement.stockable?.name }}</td>
                                                <td>{{ movement.type }}</td>
                                                <td v-if="movement.action">{{ movement?.action_type }}: {{ movement.action?.reference }}</td>
                                                <td v-else>-</td>
                                                <td>{{ movement.quantity }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-xs btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.stock-movements.show', [movement.id])"> Details</Link>
                                                            </li>
                                                            <li>
                                                                <Link :href="route('backoffice.stock-movements.edit', [movement.id])"> Edit</Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deleteStockMovement(movement)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="7" class="text-center">No stock movements found.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="props.stockMovements" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <teleport to="body">
            <StockTransferDialog :stores="props.stores" />
        </teleport>
    </BackofficeLayout>
</template>
