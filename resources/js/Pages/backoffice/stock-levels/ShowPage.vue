<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useStockLevels from '@/composables/useStockLevels';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    stockLevel: {
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
        title: 'Stock Levels',
        href: route('backoffice.stock-levels.index'),
    },
    {
        title: `Stock Level #${props.stockLevel.id}`,
        href: null,
    },
];

const { deleteStockLevel } = useStockLevels();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Stock Level Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Stock Level Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permisssions.includes('update-stock-levels')"
                    :href="route('backoffice.stock-levels.edit', [stockLevel.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permisssions.includes('delete-stock-levels')"
                    type="button"
                    @click="deleteStockLevel(stockLevel)"
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
                                    <th>ID</th>
                                    <td>{{ stockLevel.id }}</td>
                                </tr>
                                <tr>
                                    <th>Store</th>
                                    <td>
                                        <Link :href="route('backoffice.stores.show', [stockLevel.store.id])">
                                            {{ stockLevel.store.name }}
                                        </Link>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Item</th>
                                    <td>{{ stockLevel.stockable?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td>{{ stockLevel.quantity }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(stockLevel.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(stockLevel.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Deleted At</th>
                                    <td>{{ stockLevel.deleted_at ?? 'Not Deleted' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
