<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useStores from '@/composables/useStores';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    store: {
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
        title: 'Stores',
        href: route('backoffice.stores.index'),
    },
    {
        title: props.store.name,
        href: null,
    },
];

const { deleteStore } = useStores();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Store Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Store Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('update-stores')"
                    :href="route('backoffice.stores.edit', [store.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions.includes('delete-stores')"
                    type="button"
                    @click="deleteStore(store)"
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
                                    <th>{{ store.author?.name ?? '-' }}</th>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ store.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ store.name }}</td>
                                </tr>
                                <tr>
                                    <th>Short Name</th>
                                    <td>{{ store.short_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ store.address ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Location</th>
                                    <td>{{ store.location ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ store?.email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ store?.phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Paybill</th>
                                    <td>{{ store?.paybill ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Account Number</th>
                                    <td>{{ store?.account_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Till</th>
                                    <td>{{ store?.till ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>KRA PIN</th>
                                    <td>{{ store?.kra_pin ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Methods</th>
                                    <td>
                                        <span v-if="store.payment_methods.length">
                                            <div class="overflow-x-auto">
                                                <table class="table-sm table w-full">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Name</th>
                                                            <th>Phone Number</th>
                                                            <th>Account Number</th>
                                                            <th>Paybill</th>
                                                            <th>Till</th>
                                                            <th>Account Name</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(method, index) in store.payment_methods" :key="index">
                                                            <td>{{ index + 1 }}</td>
                                                            <td>{{ method.name }}</td>
                                                            <td>{{ method.pivot.phone_number ?? method?.phone_number ?? '-' }}</td>
                                                            <td>{{ method.pivot.account_number ?? method?.account_number ?? '-' }}</td>
                                                            <td>{{ method.pivot.paybill_number ?? method?.paybill_number ?? '-' }}</td>
                                                            <td>{{ method.pivot.till_number ?? method?.till_number ?? '-' }}</td>
                                                            <td>{{ method.pivot.account_name ?? method?.account_name ?? '-' }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(store.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(store.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
