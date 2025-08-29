<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import useOrganizationCategories from '@/composables/useOrganizationCategories';
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
    categories: {
        type: Object,
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
        title: 'Organization Categories',
        href: null,
    },
];

const { deleteOrganizationCategory } = useOrganizationCategories();

const { formatDate } = useDate();

const filters = reactive({
    ...props.params,
});

watch(
    () => filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.organization-categories.index'),
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
        <title>Organization Categories</title>
        <meta name="description" content="Organization Categories Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Organization Categories">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('create-organization-categories')"
                    :href="route('backoffice.organization-categories.create')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span class="hidden md:inline">New</span>
                </Link>
                <Link
                    v-if="auth.permissions?.includes('import-organization-categories')"
                    :href="route('backoffice.organization-categories.import')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span class="hidden md:inline">Import</span>
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
                                <Link
                                    :href="route('backoffice.organization-categories.index')"
                                    class="btn btn-sm btn-primary btn-outline rounded-full"
                                >
                                    <font-awesome-icon icon="times" />
                                    <span>Clear</span>
                                </Link>

                                <a
                                    v-if="auth.permissions?.includes('export-organization-categories')"
                                    :href="route('backoffice.organization-categories.export')"
                                    class="btn btn-sm btn-primary btn-outline rounded-full"
                                    download
                                >
                                    <Download class="h-4 w-4" />
                                    <span class="hidden md:inline">Export</span>
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
                                            <th>Created At</th>
                                            <th>Updated At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(category, index) in categories.data" :key="category.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>
                                                <Link :href="route('backoffice.organization-categories.show', [category?.id])" class="text-primary">{{
                                                    category.name
                                                }}</Link>
                                            </td>
                                            <td>{{ formatDate(category.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td>{{ formatDate(category.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td>
                                                <div class="dropdown dropdown-end">
                                                    <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                        <font-awesome-icon icon="ellipsis-vertical" />
                                                    </div>
                                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                        <li>
                                                            <Link :href="route('backoffice.organization-categories.edit', [category.id])"> Edit</Link>
                                                        </li>
                                                        <li><a href="#" @click="deleteOrganizationCategory(category)">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <app-pagination :resource="categories" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
