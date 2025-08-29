<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useTransactions from '@/composables/useTransactions';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    transaction: {
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
        title: 'Transactions',
        href: route('backoffice.transactions.index'),
    },
    {
        title: `# ${props.transaction.local_reference}`,
        href: null,
    },
];

const { deleteTransaction } = useTransactions();

const { formatDate } = useDate();
</script>
<template>
    <Head>
        <title>Transaction Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Transaction Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('update-transactions')"
                    :href="route('backoffice.transactions.edit', [transaction.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions?.includes('delete-transactions')"
                    type="button"
                    @click="deleteTransaction(transaction)"
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
                                    <td>{{ transaction.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ transaction.id }}</td>
                                </tr>
                                <tr>
                                    <th>Payment</th>
                                    <td>{{ transaction.payment_id ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Transaction Method</th>
                                    <td>{{ transaction.transaction_method }}</td>
                                </tr>
                                <tr>
                                    <th>Local Reference</th>
                                    <td>{{ transaction.local_reference }}</td>
                                </tr>
                                <tr>
                                    <th>Till</th>
                                    <td>{{ transaction.till ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Paybill</th>
                                    <td>{{ transaction.paybill ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Account Number</th>
                                    <td>{{ transaction.account_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <td>{{ transaction.amount }}</td>
                                </tr>
                                <tr>
                                    <th>Reference</th>
                                    <td>{{ transaction.reference ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone Number</th>
                                    <td>{{ transaction.phone_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ transaction.status ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Data</th>
                                    <td>
                                        <pre v-if="transaction.data" class="whitespace-pre-wrap">{{ transaction.data }}</pre>
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(transaction.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(transaction.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
