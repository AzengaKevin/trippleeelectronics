<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
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
</script>

<template>
    <Head>
        <title>Reservation Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Reservation Details">
        <div class="space-y-4">
            <app-alert :feedback="feedback" />

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
    </BackofficeLayout>
</template>
