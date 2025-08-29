<script setup>
import useDate from '@/composables/useDate';
import useOrders from '@/composables/useOrders';
import usePrice from '@/composables/usePrice';
import { Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { onMounted, reactive, watch } from 'vue';

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['closeDialog']);

const { orders, fetchOrders, partiallyUpdateOrder } = useOrders();
const { formatDate } = useDate();
const { formatPrice } = usePrice();

const filters = reactive({
    status: 'pending',
    store: props.store.id,
    withOutstandingAmount: true,
});

onMounted(async () => {
    fetchOrders({ ...filters });
});

watch(
    filters,
    debounce(async (newFilters) => {
        fetchOrders({ ...newFilters });
    }, 500),
    { deep: true },
);

watch(
    () => props.store,
    async (newStore) => {
        console.log(newStore);

        filters.store = newStore.id;

        await fetchOrders({ ...filters });
    },
);

const cancelOrder = (order) => {
    const resultOne = confirm("You're about to return goods?");

    if (resultOne) {
        const resultTwo = confirm('Are you sure you want to return goods?');

        if (resultTwo) {
            const resultThree = confirm('Proceed with returning goods!');

            if (resultThree) {
                partiallyUpdateOrder(order, { order_status: 'canceled' });
            }
        }
    }
};
</script>
<template>
    <div class="modal-box max-h-[90vh] min-h-[90vh] w-11/12 max-w-5xl space-y-4 md:max-h-[75vh] md:min-h-[75vh]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-2xl font-bold underline underline-offset-8">Pay Later Orders</h3>

            <button class="btn btn-ghost btn-circle" type="button" @click="$emit('closeDialog')">
                <font-awesome-icon icon="times" size="lg" />
            </button>
        </div>

        <div class="flex flex-col gap-1 md:flex-row">
            <input type="text" class="input input-sm input-bordered w-full" placeholder="Query" v-model="filters.query" />
            <input type="date" class="input input-sm input-bordered w-full" v-model="filters.from" placeholder="From" />
            <input type="date" class="input input-sm input-bordered w-full" v-model="filters.to" placeholder="To" />
        </div>

        <div class="overflow-x-auto">
            <table class="table-sm table w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ref</th>
                        <th>Time</th>
                        <th>Author</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Outstanding</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="orders.length">
                        <tr v-for="(order, index) in orders" :key="order.id">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <Link :href="route('backoffice.pos', { reference: order.reference })" class="text-primary font-bold hover:underline">
                                    {{ order.reference }}
                                </Link>
                            </td>
                            <td>{{ formatDate(order.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                            <td>{{ order.author?.name ?? '-' }}</td>
                            <td>{{ order.customer?.name ?? '-' }}</td>
                            <td>{{ formatPrice(order.total_amount) }}</td>
                            <td>{{ formatPrice(order.total_amount - order.paid_amount) }}</td>
                            <td>{{ order.order_status }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-error btn-outline" @click="cancelOrder(order)">
                                    <font-awesome-icon icon="undo" />
                                    Return&nbsp;Goods
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td colspan="8" class="text-center">No pay later orders found.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
