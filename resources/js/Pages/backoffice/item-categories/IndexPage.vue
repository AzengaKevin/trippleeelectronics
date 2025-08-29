<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useItemCategories from '@/composables/useItemCategories';
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
        title: 'Item Categories',
        href: null,
    },
];

const { deleteItemCategory } = useItemCategories();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.item-categories.index'),
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

const resetFilters = () => {
    filters.query = undefined;

    router.get(
        route('backoffice.item-categories.index'),
        {
            ...filters,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};
</script>
<template>
    <Head>
        <title>Item Categories</title>
        <meta name="description" content="Item Categories Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Item Categories">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-item-categories')"
                    :href="route('backoffice.item-categories.create')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span class="hidden lg:inline">New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('import-item-categories')"
                    :href="route('backoffice.item-categories.import')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span class="hidden lg:inline">Import</span>
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
                            <hr />

                            <div class="flex flex-wrap gap-3">
                                <button type="button" class="btn btn-sm btn-warning btn-outline rounded-full" @click="resetFilters">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </button>
                                <a
                                    v-if="auth.permissions.includes('export-item-categories')"
                                    :href="route('backoffice.item-categories.export', filters)"
                                    class="btn btn-sm btn-primary btn-outline rounded-full"
                                    download
                                >
                                    <Download class="h-4 w-4" />
                                    <span class="hidden lg:inline">Export</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Parent</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="categories.data.length">
                                            <tr v-for="(category, index) in categories.data" :key="category.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>{{ category.parent ?? '-' }}</td>
                                                <td>
                                                    <Link :href="route('backoffice.item-categories.show', [category?.id])" class="text-primary">{{
                                                        category.name
                                                    }}</Link>
                                                </td>
                                                <td>{{ category.description ?? '-' }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.item-categories.edit', [category.id])"> Edit</Link>
                                                            </li>
                                                            <li><a href="#" @click="deleteItemCategory(category)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="5">No categories results.</td>
                                            </tr>
                                        </template>
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
