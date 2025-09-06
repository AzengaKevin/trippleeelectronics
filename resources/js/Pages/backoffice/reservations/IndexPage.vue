<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import usePrice from '@/composables/usePrice';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    reservations: {
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
    { title: 'Reservations', href: null },
];

const filters = reactive({
    ...props.params,
});

const { formatPrice } = usePrice();
const { formatDate } = useDate();

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.reservations.index'), newFilters);
    }, 300),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Reservations</title>
    </Head>
    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Reservations">
        <div class="flex flex-wrap">
            <Link :href="route('backoffice.reservations.create')" class="btn btn-sm btn-primary btn-outline rounded-full">
                <font-awesome-icon icon="plus" /> Reservation
            </Link>
        </div>
        <div class="grid grid-cols-12 items-start gap-4">
            <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                <div class="card bg-[#87ceeb]">
                    <div class="card-body space-y-4">
                        <h2 class="test-xl font-bold">Filter</h2>

                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text">Date</span>
                            </label>
                            <input type="date" class="input input-bordered w-full" v-model="filters.date" placeholder="From" />
                        </div>
                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text">Name</span>
                            </label>
                            <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                        </div>
                        <hr />
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('backoffice.reservations.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
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
                            <table class="table">
                                <thead>
                                    <tr class="uppercase">
                                        <th>#</th>
                                        <th>Reference</th>
                                        <th>Author</th>
                                        <th>Primary Guest</th>
                                        <th>Amount</th>
                                        <th>Guests</th>
                                        <th>Rooms</th>
                                        <th>Status</th>
                                        <th>Paid</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="props.reservations.data.length">
                                        <tr v-for="(reservation, index) in props.reservations.data" :key="reservation.id">
                                            <td>{{ index + props.reservations.from }}</td>
                                            <td>{{ reservation.reference }}</td>
                                            <td>{{ reservation?.author?.name ?? '-' }}</td>
                                            <td>{{ reservation?.primary_individual?.name ?? '-' }}</td>
                                            <td>{{ reservation?.total_amount ?? '-' }}</td>
                                            <td>{{ reservation?.guests_count ?? '-' }}</td>
                                            <td>{{ reservation?.rooms_count ?? '-' }}</td>
                                            <td>{{ reservation?.status ?? '-' }}</td>
                                            <td>-</td>
                                            <td>
                                                <div class="dropdown dropdown-end">
                                                    <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                        <font-awesome-icon icon="ellipsis-vertical" />
                                                    </div>
                                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                        <li>
                                                            <Link :href="route('backoffice.reservations.show', reservation.id)"> Details</Link>
                                                        </li>
                                                        <li>
                                                            <Link :href="'#'"> Edit </Link>
                                                        </li>
                                                        <li><a href="#" role="button">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td colspan="10" class="text-center">No reservations found.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <app-pagination :resource="props.reservations" />
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
