<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import usePayments from '@/composables/usePayments';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    payments: {
        type: Object,
        required: true,
    },
    stores: {
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
    },
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Payments',
        href: null,
    },
];

const { deletePayment } = usePayments();

const { formatDate } = useDate();

const filters = reactive({ ...props.params });

watch(
    () => filters,
    debounce((newFilters) => {
        router.get(route('backoffice.payments.index'), newFilters);
    }, 500),
    { deep: true },
);
</script>
<template>
    <Head>
        <title>Payments</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Payments">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('browse-payments')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                    :href="route('backoffice.payments.create')"
                >
                    <font-awesome-icon icon="plus" />
                    New
                </Link>
                <Link
                    v-if="auth.permissions?.includes('import-payments')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                    :href="route('backoffice.payments.import')"
                >
                    <font-awesome-icon icon="upload" />
                    Import
                </Link>
            </div>

            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-[#87ceeb]">
                        <div class="card-body space-y-2">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Search</span>
                                </label>
                                <input
                                    type="text"
                                    class="input input-sm input-bordered w-full"
                                    placeholder="Search for Payment"
                                    v-model="filters.query"
                                />
                            </div>
                            <div v-if="auth.user.roles.map((r) => r.name).includes('admin')" class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Store</span>
                                </label>
                                <select class="select select-sm select-bordered w-full" v-model="filters.store">
                                    <option :value="undefined">All</option>
                                    <template v-for="store in props.stores" :key="store.id">
                                        <option :value="store.id">{{ store.name }}</option>
                                    </template>
                                </select>
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.payments.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </Link>
                                <a
                                    v-if="auth.permissions?.includes('export-payments')"
                                    class="btn btn-sm btn-primary btn-outline rounded-full"
                                    :href="route('backoffice.payments.export', { ...filters })"
                                    download
                                >
                                    <font-awesome-icon icon="download" />
                                    Export
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-blue-200 shadow">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto">
                                <table class="table-sm table w-full">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Author</th>
                                            <th>Payer</th>
                                            <th>Payee</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="payments.data.length">
                                            <tr v-for="(payment, index) in payments.data" :key="payment.id">
                                                <td>{{ payments.from + index }}</td>
                                                <td>{{ payment.amount }}</td>
                                                <td>{{ payment.payment_method?.name ?? '-' }}</td>
                                                <td>{{ payment.author?.name ?? '-' }}</td>
                                                <td>{{ payment.payer?.name ?? '-' }}</td>
                                                <td>{{ payment.payee?.name ?? '-' }}</td>
                                                <td>{{ payment.status ?? '-' }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.payments.show', [payment.id])"> Details</Link>
                                                            </li>
                                                            <li>
                                                                <Link :href="route('backoffice.payments.edit', [payment.id])"> Edit</Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deletePayment(payment)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="8" class="text-center">No payments found</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <app-pagination :resource="payments" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
