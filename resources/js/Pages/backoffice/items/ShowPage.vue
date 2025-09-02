<script setup>
import useDate from '@/composables/useDate';
import useItems from '@/composables/useItems';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    item: {
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
        href: route('backoffice.items.index'),
    },
    {
        title: props.item.name,
        href: null,
    },
];

const { deleteItem } = useItems();
const { showFeedbackSwal } = useSwal();
const { formatDate } = useDate();

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
    { immediate: true },
);
</script>

<template>
    <Head>
        <title>Item Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Item Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('update-items')"
                    :href="route('backoffice.items.edit', [item.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <Link
                    v-if="auth.permissions.includes('browse-items')"
                    :href="route('backoffice.items.media.index', [item.id])"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <font-awesome-icon icon="image" />
                    Media
                </Link>
                <button
                    v-if="auth.permissions.includes('delete-items')"
                    type="button"
                    @click="deleteItem(item)"
                    class="btn btn-sm btn-error btn-outline rounded-full"
                >
                    <font-awesome-icon icon="trash-alt" />
                    Delete
                </button>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <tbody>
                                <tr>
                                    <th class="w-1/3">ID</th>
                                    <td class="w-2/3">{{ item.id }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">SKU</th>
                                    <td class="w-2/3">{{ item.sku }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Name</th>
                                    <td class="w-2/3">{{ item.name }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">POS Name</th>
                                    <td class="w-2/3">{{ item.pos_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Image</th>
                                    <td class="w-2/3">
                                        <img v-if="item.image_url" width="120" :src="item.image_url" :alt="item.name" />
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Slug</th>
                                    <td class="w-2/3">{{ item.slug }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Brand</th>
                                    <td class="w-2/3">{{ item.brand?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Category</th>
                                    <td class="w-2/3">{{ item.category?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Price</th>
                                    <td class="w-2/3">{{ item.price ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Selling Price</th>
                                    <td class="w-2/3">{{ item.selling_price ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Cost</th>
                                    <td class="w-2/3">{{ item.cost ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Quantity</th>
                                    <td class="w-2/3">{{ item.quantity ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Description</th>
                                    <td class="w-2/3">{{ item.description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">POS Description</th>
                                    <td class="w-2/3">{{ item.pos_description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Created At</th>
                                    <td class="w-2/3">{{ formatDate(item.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3">Updated At</th>
                                    <td class="w-2/3">{{ formatDate(item.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
