<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useItemVariants from '@/composables/useItemVariants';
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
    itemVariants: {
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
        title: 'Item Variants',
        href: null,
    },
];

const { deleteItemVariant } = useItemVariants();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.item-variants.index'), newFilters, { preserveState: true, replace: true });
    }, 300),
    { deep: true },
);

const resetFilters = () => {
    filters.query = undefined;

    router.get(route('backoffice.item-variants.index'), filters, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head>
        <title>Item Variants</title>
        <meta name="description" content="Item Variants Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Item Variants">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('browse-item-variants')"
                    :href="route('backoffice.item-variants.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('import-item-variants')"
                    :href="route('backoffice.item-variants.import')"
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

                            <div class="flex flex-wrap">
                                <button type="button" class="btn btn-sm btn-primary btn-outline rounded-full" @click="resetFilters">
                                    <font-awesome-icon icon="times" />
                                    <span>Clear</span>
                                </button>

                                <a
                                    v-if="auth.permissions.includes('export-item-variants')"
                                    :href="route('backoffice.item-variants.export', filters)"
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
                                            <th>Variant Name</th>
                                            <th>Item</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="props.itemVariants.data.length === 0">
                                            <tr>
                                                <td colspan="6" class="text-center">No item variants results.</td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr v-for="(variant, index) in props.itemVariants.data" :key="variant.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>
                                                    <Link :href="route('backoffice.item-variants.show', [variant.id])" class="text-primary">{{
                                                        variant.name
                                                    }}</Link>
                                                </td>
                                                <td>{{ variant.item?.name ?? '-' }}</td>
                                                <td>{{ variant.price }}</td>
                                                <td>{{ variant.quantity ?? '-' }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.item-variants.edit', [variant.id])"> Edit</Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deleteItemVariant(variant)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
