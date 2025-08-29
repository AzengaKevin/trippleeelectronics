<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import usePayments from '@/composables/usePayments';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    payment: {
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
        title: 'Payments',
        href: route('backoffice.payments.index'),
    },
    {
        title: `# ${props.payment.id}`,
        href: null,
    },
];

const { deletePayment } = usePayments();
const { formatDate } = useDate();
</script>
<template>
    <Head>
        <title>Payment Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Payment Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('update-payments')"
                    :href="route('backoffice.payments.edit', [payment.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions?.includes('delete-payments')"
                    type="button"
                    @click="deletePayment(payment)"
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
                                    <th>Payment ID</th>
                                    <td>{{ payment.id }}</td>
                                </tr>
                                <tr>
                                    <th>Author</th>
                                    <td>{{ payment?.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <td>{{ payment.amount }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Method</th>
                                    <td>{{ payment.payment_method ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Payer</th>
                                    <td>{{ payment?.payer?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Payee</th>
                                    <td>{{ payment?.payee?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Payment For:</th>
                                    <td v-if="payment.payable">{{ payment.payable_type }} #{{ payment.reference }}</td>
                                    <td v-else>-</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ payment.status }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(payment.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(payment.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
