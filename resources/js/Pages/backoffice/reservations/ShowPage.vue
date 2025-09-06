<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import usePrice from '@/composables/usePrice';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    reservation: {
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
        title: 'Reservations',
        href: route('backoffice.reservations.index'),
    },
    {
        title: props.reservation.id,
        href: null,
    },
];

const { formatDate } = useDate();
const { formatPrice } = usePrice();
</script>

<template>
    <Head>
        <title>Reservation Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Reservation Details">
        <div class="space-y-4">
            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-2">
                <div class="col-span-12 md:col-span-6">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <tbody>
                                        <tr>
                                            <th>Author</th>
                                            <td>{{ reservation.author?.name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>ID</th>
                                            <td>{{ reservation.id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Reference</th>
                                            <td>{{ reservation.reference ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Primary Guest</th>
                                            <td>{{ reservation.primary_individual?.name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Check-in Date</th>
                                            <td>{{ formatDate(reservation.checkin_date, 'YY-MM-DD') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Check-out Date</th>
                                            <td>{{ formatDate(reservation.checkout_date, 'YY-MM-DD') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Guests Count</th>
                                            <td>{{ reservation.guests_count ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Rooms Count</th>
                                            <td>{{ reservation.rooms_count ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount</th>
                                            <td>{{ reservation.total_amount ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tendered Amount</th>
                                            <td>{{ reservation.tendered_amount ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Balance Amount</th>
                                            <td>{{ reservation.balance_amount ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>{{ reservation.status ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ formatDate(reservation.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ formatDate(reservation.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 flex flex-col gap-2 md:sticky md:top-0 md:col-span-6">
                    <div class="card bg-blue-200 shadow">
                        <div class="card-body space-y-4">
                            <h2 class="text-xl font-semibold">Allocations</h2>
                            <div class="overflow-x-auto">
                                <table class="table-sm table w-full">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Room</th>
                                            <th>Type</th>
                                            <th>Occupants</th>
                                            <th>Check-in</th>
                                            <th>Check-out</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(allocation, index) in reservation.allocations" :key="allocation.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ allocation.room?.name ?? '-' }}</td>
                                            <td>{{ allocation.roomType?.name ?? '-' }}</td>
                                            <td>{{ allocation.occupants ?? '-' }}</td>
                                            <td>{{ formatDate(allocation.start, 'YY-MM-DD') }}</td>
                                            <td>{{ formatDate(allocation.end, 'YY-MM-DD') }}</td>
                                            <td>{{ formatPrice(allocation.room.price) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>#</td>
                                            <td colspan="5">Total</td>
                                            <td>
                                                {{
                                                    formatPrice(
                                                        reservation.allocations.reduce((total, allocation) => total + allocation.room?.price, 0),
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-green-200 shadow">
                        <div class="card-body space-y-4">
                            <h2 class="text-xl font-semibold">Individuals</h2>
                            <div class="overflow-x-auto">
                                <table class="table-sm table w-full">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>ID Number</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(individual, index) in reservation.individuals" :key="individual.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ individual.name ?? '-' }}</td>
                                            <td>{{ individual.email ?? '-' }}</td>
                                            <td>{{ individual.phone ?? '-' }}</td>
                                            <td>{{ individual.id_number ?? '-' }}</td>
                                            <td>-</td>
                                        </tr>
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
