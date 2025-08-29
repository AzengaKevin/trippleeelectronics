<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import usePrice from '@/composables/usePrice';
import usePurchases from '@/composables/usePurchases';
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
    purchases: {
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
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Purchases', href: null },
];

const { deletePurchase } = usePurchases();
const { formatPrice } = usePrice();
const { formatDate } = useDate();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.purchases.index'), { ...newFilters }, { preserveState: true, replace: true });
    }, 500),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Purchases</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Purchases">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-purchases')"
                    :href="route('backoffice.purchases.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('import-purchases')"
                    :href="route('backoffice.purchases.import')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span>Import</span>
                </Link>
            </div>

            <AppAlert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-[#87ceeb]">
                        <div class="card-body space-y-2">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Name</span>
                                </label>
                                <input type="text" class="input input-sm input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>
                            <div v-if="auth.user.roles.map((r) => r.name).includes('admin')" class="space-y-2">
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
                            <div class="space-y-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" class="checkbox checkbox-primary" v-model="filters.withoutStore" />
                                    <span class="label-text">Without Store</span>
                                </label>
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.purchases.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    Reset
                                </Link>
                                <a
                                    v-if="auth.permissions.includes('export-purchases')"
                                    :href="route('backoffice.purchases.export', filters)"
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
                                            <th>Supplier</th>
                                            <th>Total</th>
                                            <th>Outstanding</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="props.purchases.data.length === 0">
                                            <tr>
                                                <td colspan="8" class="text-center">No purchases found.</td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr v-for="(purchase, index) in props.purchases.data" :key="purchase.id">
                                                <td>{{ index + props.purchases.from }}</td>
                                                <td>
                                                    <Link
                                                        :href="route('backoffice.purchases.show', [purchase.id])"
                                                        class="text-primary font-bold hover:underline"
                                                    >
                                                        {{ purchase.reference }}
                                                    </Link>
                                                </td>
                                                <td>{{ formatDate(purchase.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                                <td>{{ purchase.store?.name ?? '-' }}</td>
                                                <td>{{ purchase.author?.name ?? '-' }}</td>
                                                <td>{{ purchase.supplier?.name ?? '-' }}</td>
                                                <td>{{ formatPrice(purchase.total_amount) }}</td>
                                                <td>{{ formatPrice(purchase.total_amount - purchase.paid_amount) }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.purchases.show', [purchase.id])"> Details</Link>
                                                            </li>
                                                            <li>
                                                                <Link :href="route('backoffice.purchases.edit', [purchase.id])"> Edit</Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deletePurchase(purchase)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="purchases" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
