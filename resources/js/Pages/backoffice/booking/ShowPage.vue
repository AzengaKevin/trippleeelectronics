<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    currentBuilding: {
        type: Object,
        required: true,
    },
    buildings: {
        type: Array,
        required: true,
    },
    rooms: {
        type: Object,
        required: true,
    },
    params: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        required: false,
        default: null,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Booking', href: null },
];

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.booking.show'), {
            params: newFilters,
        });
    }, 300),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Booking</title>
    </Head>
    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Booking">
        <div class="grid grid-cols-12 items-start gap-4">
            <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                <div class="card bg-[#87ceeb]">
                    <div class="card-body space-y-4">
                        <h2 class="test-xl font-bold">Filter</h2>

                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text">Date</span>
                            </label>
                            <input type="date" class="input input-bordered w-full" v-model="filters.from" placeholder="From" />
                        </div>
                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text">Name</span>
                            </label>
                            <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                        </div>
                        <hr />
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('backoffice.booking.show')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                <font-awesome-icon icon="times" />
                                Reset
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-9">
                <div class="card bg-blue-200">
                    <div class="card-body space-y-4">
                        <div class="overflow-x-auto pb-48">
                            <table class="table-lg table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Room</th>
                                        <th>Period</th>
                                        <th>Author</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="props.rooms.data.length">
                                        <tr v-for="(room, index) in props.rooms.data" :key="room.id">
                                            <td>{{ index + props.rooms.from }}</td>
                                            <td>{{ room.name }}</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">Book</button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td colspan="10" class="text-center">No orders found.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <app-pagination :resource="props.orders" />
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
