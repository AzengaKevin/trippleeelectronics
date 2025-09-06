<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import BookRoomDialog from '@/Pages/backoffice/bookings/BookRoomDialog.vue';
import UpdateBuildingForm from '@/Pages/backoffice/bookings/UpdateBuildingForm.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    currentBuilding: {
        type: Object,
        required: false,
    },
    buildings: {
        type: Array,
        required: true,
    },
    properties: {
        type: Array,
        required: true,
    },
    bookings: {
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
    { title: 'Bookings', href: null },
];

const filters = reactive({
    ...props.params,
});

const updateBuildingDialog = ref(null);

const { formatDate } = useDate();

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.bookings.show'), newFilters);
    }, 300),
    { deep: true },
);

const closeUpdateBuildingDialog = () => {
    if (updateBuildingDialog.value) {
        updateBuildingDialog.value.close();
    }
};

const currentBooking = ref(null);

const createNewBooking = (booking) => {
    currentBooking.value = booking;

    const dialog = document.getElementById('newBookingDialog');
    if (dialog) {
        dialog.showModal();
    }
};
</script>

<template>
    <Head>
        <title>Bookings</title>
    </Head>
    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Bookings">
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
                                <span class="label-text">Query</span>
                            </label>
                            <input type="text" class="input input-bordered w-full" placeholder="Query" v-model="filters.query" />
                        </div>
                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text">Property</span>
                            </label>
                            <select class="select select-bordered w-full" v-model="filters.property">
                                <option :value="undefined">All Properties</option>
                                <option v-for="property in properties" :key="property.id" :value="property.id">
                                    {{ property.name }}
                                </option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text">Building</span>
                            </label>
                            <select class="select select-bordered w-full" v-model="filters.building">
                                <option :value="undefined">All Buildings</option>
                                <option v-for="building in buildings" :key="building.id" :value="building.id">
                                    {{ building.name }}
                                </option>
                            </select>
                        </div>
                        <hr />
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('backoffice.bookings.show')" class="btn btn-sm btn-primary btn-outline rounded-full">
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
                                        <th>Room</th>
                                        <th>Booked</th>
                                        <th>Period</th>
                                        <th>Duration</th>
                                        <th>Author</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="props.bookings.data.length">
                                        <tr v-for="(booking, index) in props.bookings.data" :key="booking.id">
                                            <td>{{ index + props.bookings.from }}</td>
                                            <td class="uppercase">{{ booking.name }}</td>
                                            <td>
                                                <span v-if="booking.allocation_id" class="text-error"><font-awesome-icon icon="times" /></span>
                                                <span v-else class="text-success"><font-awesome-icon icon="check" /></span>
                                            </td>
                                            <td>
                                                <span>{{ formatDate(booking.allocation?.start) }}</span> -
                                                <span>{{ formatDate(booking.allocation?.end) }}</span>
                                            </td>
                                            <td>
                                                <span v-if="booking.period_days && booking.period_days > 0">{{ booking.period_days }} days</span>
                                                <span v-else-if="booking.period_hours && booking.period_hours > 0"
                                                    >{{ booking.period_hours }} hours</span
                                                >
                                                <span v-if="!booking.period_days && !booking.period_hours">-</span>
                                            </td>
                                            <td>{{ booking.allocation?.reservation?.author?.name ?? '-' }}</td>
                                            <td>{{ booking.allocation?.reservation?.primary_individual?.name ?? '-' }}</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>
                                                <button
                                                    @click="createNewBooking(booking)"
                                                    class="btn btn-sm btn-primary"
                                                    :disabled="!!booking.allocation_id"
                                                >
                                                    <font-awesome-icon icon="calendar" />
                                                    Book
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td colspan="10" class="text-center">No bookings found.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <app-pagination :resource="props.bookings" />
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>

    <teleport to="body">
        <dialog ref="updateBuildingDialog" class="modal">
            <update-building-form :buildings="buildings" :building="currentBuilding" @closeDialog="closeUpdateBuildingDialog" />
        </dialog>
        <BookRoomDialog :current-booking="currentBooking" />
    </teleport>
</template>
