<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import usePrice from '@/composables/usePrice';
import useQuotations from '@/composables/useQuotations';
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
    quotations: {
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
    { title: 'Quotations', href: null },
];

const { formatPrice } = usePrice();

const { deleteQuotation } = useQuotations();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.quotations.index'), { ...newFilters }, { preserveState: true, replace: true });
    }, 500),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Quotations</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Quotations">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-quotations')"
                    :href="route('backoffice.quotations.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('import-quotations')"
                    :href="route('backoffice.quotations.import')"
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
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>
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
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.quotations.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    Reset
                                </Link>

                                <a
                                    v-if="auth.permissions.includes('export-quotations')"
                                    :href="route('backoffice.quotations.export')"
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
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-xs table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Reference</th>
                                            <th>Store</th>
                                            <th>Customer</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="props.quotations.data.length">
                                            <tr v-for="(quotation, index) in props.quotations.data" :key="quotation.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>
                                                    <Link
                                                        :href="route('backoffice.quotations.show', [quotation.id])"
                                                        class="text-primary font-bold hover:underline"
                                                    >
                                                        {{ quotation.reference }}
                                                    </Link>
                                                </td>
                                                <td>{{ quotation.store?.name ?? '-' }}</td>
                                                <td>{{ quotation.customer?.name ?? '-' }}</td>
                                                <td>{{ formatPrice(quotation.total_amount) }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.quotations.show', [quotation.id])"> Details</Link>
                                                            </li>
                                                            <li>
                                                                <Link :href="route('backoffice.quotations.edit', [quotation.id])"> Edit </Link>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="6" class="text-center">No orders found.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="props.quotations" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
