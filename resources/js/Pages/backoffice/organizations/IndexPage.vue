<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useOrganizations from '@/composables/useOrganizations';
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
    organizations: {
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
        title: 'Oraganizations',
        href: null,
    },
];

const { deleteOrganization } = useOrganizations();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.organizations.index'), newFilters, { preserveState: true, replace: true });
    }, 300),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Organizations</title>
        <meta name="description" content="Organizations Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Organizations">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-organizations')"
                    :href="route('backoffice.organizations.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('import-organizations')"
                    :href="route('backoffice.organizations.import')"
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

                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Category</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.category">
                                    <option :value="undefined">All</option>
                                    <option v-for="category in props.categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                </select>
                            </div>

                            <hr />

                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.organizations.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Clear</span>
                                </Link>

                                <a
                                    v-if="auth.permissions.includes('export-organizations')"
                                    :href="route('backoffice.organizations.export')"
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
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(organization, index) in props.organizations.data" :key="organization.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>
                                                <Link :href="route('backoffice.organizations.show', [organization.id])" class="text-primary">{{
                                                    organization.name
                                                }}</Link>
                                            </td>
                                            <td>{{ organization.organization_category?.name ?? '-' }}</td>
                                            <td>{{ organization.phone ?? '-' }}</td>
                                            <td>{{ organization.email ?? '-' }}</td>
                                            <td>
                                                <div class="dropdown dropdown-end">
                                                    <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                        <font-awesome-icon icon="ellipsis-vertical" />
                                                    </div>
                                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                        <li>
                                                            <Link :href="route('backoffice.organizations.edit', [organization.id])"> Edit</Link>
                                                        </li>
                                                        <li><a href="#" role="button" @click="deleteOrganization(organization)">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="organizations" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
