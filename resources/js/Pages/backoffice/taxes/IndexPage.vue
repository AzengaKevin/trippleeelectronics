<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import useTaxes from '@/composables/useTaxes';
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
    taxes: {
        type: Object,
        required: true,
    },
    params: {
        type: Object,
        required: true,
    },
    feedback: Object,
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Taxes',
        href: null,
    },
];

const { deleteTax } = useTaxes();

const { formatDate } = useDate();

const filters = reactive({
    ...props.params,
});

watch(
    () => filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.taxes.index'),
            {
                ...newFilters,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 500),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Taxes</title>
        <meta name="description" content="Taxes Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Taxes">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('create-taxes')"
                    :href="route('backoffice.taxes.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions?.includes('import-taxes')"
                    :href="route('backoffice.taxes.import')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span>Import</span>
                </Link>
            </div>

            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 order-first col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <h2 class="text-lg font-semibold">Filters</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Name</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>

                            <hr />

                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.taxes.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Clear</span>
                                </Link>
                                <a
                                    v-if="auth.permissions?.includes('export-taxes')"
                                    :href="route('backoffice.taxes.export')"
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
                    <div class="card bg-base-100 shadow">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-xs table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Rate</th>
                                            <th>Compound</th>
                                            <th>Inclusive</th>
                                            <th>Created At</th>
                                            <th>Updated At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(tax, index) in props.taxes.data" :key="tax.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>
                                                <Link :href="route('backoffice.taxes.show', [tax.id])" class="text-primary">
                                                    {{ tax.name }}
                                                </Link>
                                            </td>
                                            <td>{{ tax.rate }}</td>
                                            <td class="uppercase">{{ tax.is_compound }}</td>
                                            <td class="uppercase">{{ tax.is_inclusive }}</td>
                                            <td>{{ formatDate(tax.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td>{{ formatDate(tax.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td>
                                                <div class="dropdown dropdown-end">
                                                    <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                        <font-awesome-icon icon="ellipsis-vertical" />
                                                    </div>
                                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                        <li v-if="auth.permissions?.includes('update-taxes')">
                                                            <Link :href="route('backoffice.taxes.edit', [tax.id])"> Edit</Link>
                                                        </li>
                                                        <li v-if="auth.permissions?.includes('delete-taxes')">
                                                            <a href="#" role="button" @click="deleteTax(tax)">Delete</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="taxes" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
