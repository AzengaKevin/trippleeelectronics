<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useItems from '@/composables/useItems';
import usePrice from '@/composables/usePrice';
import useSwal from '@/composables/useSwal';
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
        title: 'Items',
        href: null,
    },
];

const { deleteItem } = useItems();
const { showFeedbackSwal } = useSwal();
const { formatPrice } = usePrice();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.items.index'), newFilters, { preserveState: true, replace: true });
    }, 300),
    { deep: true },
);

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
);
</script>

<template>
    <Head>
        <title>Items</title>
        <meta name="description" content="Items Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Items">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('browse-items')"
                    :href="route('backoffice.items.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('create-items')"
                    :href="route('backoffice.items.import')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span>Import</span>
                </Link>
            </div>

            <div class="grid grid-cols-12 items-start gap-2">
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-sm table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Brand</th>
                                            <th class="text-center">Cost</th>
                                            <th class="text-center">Price</th>
                                            <th>On Hand</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="props.items.data.length">
                                            <tr v-for="(item, index) in props.items.data" :key="item.id">
                                                <td>{{ index + props.items.from }}</td>
                                                <td>
                                                    <Link :href="route('backoffice.items.show', [item.id])" class="text-primary">{{
                                                        item.name
                                                    }}</Link>
                                                </td>
                                                <td>{{ item.category?.name ?? '-' }}</td>
                                                <td>{{ item.brand?.name ?? '-' }}</td>
                                                <td class="text-end font-bold">{{ formatPrice(item.cost) }}</td>
                                                <td class="text-end font-bold">{{ formatPrice(item.price) }}</td>
                                                <td>{{ item.quantity ?? '-' }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.items.edit', [item.id])"> Edit </Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deleteItem(item)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="8" class="text-center">No items found.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <app-pagination :resource="items" />
                        </div>
                    </div>
                </div>
                <div class="top-0 order-first col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-[#87ceeb] shadow">
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
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Brand</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.brand">
                                    <option :value="undefined">All</option>
                                    <option v-for="brand in props.brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" v-model="filters.withoutMedia" :value="true" class="checkbox checkbox-primary" />
                                    <span>Without Media</span>
                                </label>
                            </div>

                            <div class="space-y-2">
                                <label class="flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" v-model="filters.outOfStock" :value="true" class="checkbox checkbox-primary" />
                                    <span>Out of Stock</span>
                                </label>
                            </div>

                            <hr />

                            <div class="flex flex-wrap gap-3">
                                <Link type="button" class="btn btn-sm btn-primary btn-outline rounded-full" :href="route('backoffice.items.index')">
                                    <font-awesome-icon icon="times" />
                                    <span>Clear</span>
                                </Link>

                                <a
                                    v-if="auth.permissions.includes('export-items')"
                                    :href="route('backoffice.items.export', filters)"
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
    </BackofficeLayout>
</template>
