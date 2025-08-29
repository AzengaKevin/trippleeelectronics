<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import usePaymentMethods from '@/composables/usePaymentMethods';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    method: {
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
        title: 'Payment Methods',
        href: route('backoffice.payment-methods.index'),
    },
    {
        title: props.method.name,
        href: null,
    },
];

const { formatDate } = useDate();
const { deletePaymentMethod } = usePaymentMethods();
</script>
<template>
    <Head>
        <title>Payment Method Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Payment Method Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('update-payment-methods')"
                    :href="route('backoffice.payment-methods.edit', [method.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions?.includes('delete-payment-methods')"
                    type="button"
                    @click="deletePaymentMethod(payment)"
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
                                    <td>{{ method.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ method.name }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ method.description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Require Fields</th>
                                    <td>{{ method.required_fields?.length ? method.required_fields.join(', ') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone Number</th>
                                    <td>{{ method.phone_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Paybill Number</th>
                                    <td>{{ method.paybill_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Account Number</th>
                                    <td>{{ method.account_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Till Number</th>
                                    <td>{{ method.till_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Account Name</th>
                                    <td>{{ method.account_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Properties</th>
                                    <td>
                                        <pre>{{ method.properties }}</pre>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(method.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(method.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
