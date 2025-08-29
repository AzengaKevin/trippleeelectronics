<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useItemVariants from '@/composables/useItemVariants';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    itemVariant: {
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
        href: route('backoffice.item-variants.index'),
    },
    {
        title: props.itemVariant.name,
        href: null,
    },
];

const { deleteItemVariant } = useItemVariants();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Item Variant Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Item Variant Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('update-item-variants')"
                    :href="route('backoffice.item-variants.edit', [itemVariant.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions.includes('delete-item-variants')"
                    type="button"
                    @click="deleteItemVariant(itemVariant)"
                    class="btn btn-sm btn-error btn-outline rounded-full"
                >
                    <font-awesome-icon icon="trash-alt" />
                    Delete
                </button>
            </div>

            <app-alert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <tbody>
                                <tr>
                                    <th>Author</th>
                                    <td>{{ itemVariant.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ itemVariant.id }}</td>
                                </tr>
                                <tr>
                                    <th>SKU</th>
                                    <td>{{ itemVariant.sku }}</td>
                                </tr>
                                <tr>
                                    <th>Item</th>
                                    <td>
                                        <Link :href="route('backoffice.items.show', [itemVariant.item.id])">
                                            {{ itemVariant.item.name }}
                                        </Link>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Attribute</th>
                                    <td>{{ itemVariant.attribute }}</td>
                                </tr>
                                <tr>
                                    <th>Value</th>
                                    <td>{{ itemVariant.value }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ itemVariant.name }}</td>
                                </tr>
                                <tr>
                                    <th>POS Name</th>
                                    <td>{{ itemVariant.pos_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Image</th>
                                    <td>
                                        <img v-if="itemVariant.image_url" width="120" :src="itemVariant.image_url" :alt="itemVariant.name" />
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Price</th>
                                    <td>{{ itemVariant.price ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Cost</th>
                                    <td>{{ itemVariant.cost ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td>{{ itemVariant.quantity ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Variant Description</th>
                                    <td>{{ itemVariant.description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>POS Description</th>
                                    <td>{{ itemVariant.pos_description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(itemVariant.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(itemVariant.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
