<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import useStores from '@/composables/useStores';
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
    stores: {
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
        title: 'Stores',
        href: null,
    },
];

const { deleteStore } = useStores();

const { formatDate } = useDate();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.stores.index'), newFilters, {
            preserveState: true,
            replace: true,
        });
    }, 500),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Stores</title>
        <meta name="description" content="Stores Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Stores">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-stores')"
                    :href="route('backoffice.stores.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('import-stores')"
                    :href="route('backoffice.stores.import')"
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
                                <Link :href="route('backoffice.stores.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </Link>

                                <a
                                    v-if="auth.permissions.includes('export-stores')"
                                    :href="route('backoffice.stores.export', filters)"
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
                                <table class="table-xs table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Address</th>
                                            <th>Location</th>
                                            <th>Created At</th>
                                            <th>Updated At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(store, index) in props.stores.data" :key="store.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>
                                                <Link :href="route('backoffice.stores.show', [store.id])" class="text-primary">
                                                    {{ store.name }}</Link
                                                >
                                            </td>
                                            <td>{{ store.address }}</td>
                                            <td>{{ store.location }}</td>
                                            <td>{{ formatDate(store.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td>{{ formatDate(store.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td>
                                                <div class="dropdown dropdown-end">
                                                    <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                        <font-awesome-icon icon="ellipsis-vertical" />
                                                    </div>
                                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                        <li v-if="auth.permissions.includes('update-stores')">
                                                            <Link :href="route('backoffice.stores.edit', [store.id])"> Edit </Link>
                                                        </li>
                                                        <li v-if="auth.permissions.includes('delete-stores')">
                                                            <a href="#" role="button" @click="deleteStore(store)">Delete</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="stores" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
