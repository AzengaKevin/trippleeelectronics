<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useStockMovements from '@/composables/useStockMovements';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    stockMovement: {
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
        title: 'Stock Movements',
        href: route('backoffice.stock-movements.index'),
    },
    {
        title: `Stock Movement #${props.stockMovement.id}`,
        href: null,
    },
];

const { deleteStockMovement } = useStockMovements();

const { showFeedbackSwal } = useSwal();

const { formatDate } = useDate();

const routeActionName = computed(() => {
    if (props.stockMovement.action) {
        if (props.stockMovement.action_type === 'purchase') {
            return 'backoffice.purchases.show';
        }

        if (props.stockMovement.action_type === 'order') {
            return 'backoffice.orders.show';
        }
    }

    return null;
});

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
    {
        immediate: true,
    },
);
</script>

<template>
    <Head>
        <title>Stock Movement Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Stock Movement Details">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('update-stock-movements')"
                    :href="route('backoffice.stock-movements.edit', [stockMovement.id])"
                    class="btn btn-xs btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions.includes('delete-stock-movements')"
                    type="button"
                    @click="deleteStockMovement(stockLevel)"
                    class="btn btn-xs btn-error btn-outline rounded-full"
                >
                    <font-awesome-icon icon="trash-alt" />
                    Delete
                </button>
            </div>

            <app-alert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table-sm table w-full">
                            <tbody>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ stockMovement.id }}</td>
                                </tr>
                                <tr>
                                    <th>Author</th>
                                    <td>{{ stockMovement.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Store</th>
                                    <td>
                                        <Link :href="route('backoffice.stores.show', [stockMovement.store.id])" class="link link-primary">
                                            {{ stockMovement.store.name }}
                                        </Link>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Item</th>
                                    <td>{{ stockMovement.stockable?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Trashed Item</th>
                                    <td>{{ stockMovement.robust_stockable?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ stockMovement?.type ?? '-' }}</td>
                                </tr>
                                <tr v-if="stockMovement.action">
                                    <th>Action</th>
                                    <td class="capitalize">
                                        <component
                                            :is="routeActionName ? Link : 'span'"
                                            :href="route(routeActionName, [stockMovement.action.id])"
                                            class="link link-primary"
                                        >
                                            {{ stockMovement?.action_type }}: (#{{ stockMovement?.action?.reference ?? '-' }})
                                        </component>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td>{{ stockMovement.quantity }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ stockMovement.description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Cost Implication</th>
                                    <td>{{ stockMovement.cost_implication ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(stockMovement.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(stockMovement.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Deleted At</th>
                                    <td>{{ stockMovement.deleted_at ?? 'Not Deleted' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
