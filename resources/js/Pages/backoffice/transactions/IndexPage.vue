<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import useTransactions from '@/composables/useTransactions';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    transactions: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    methods: {
        type: Array,
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
        title: 'Transactions',
        href: null,
    },
];

const { deleteTransaction } = useTransactions();

const { formatDate } = useDate();

const filters = reactive({ ...props.params });

watch(
    () => filters,
    debounce((newFilters) => {
        router.get(route('backoffice.transactions.index'), newFilters, {
            preserveState: true,
            replace: true,
            onSuccess: () => {
                window.scrollTo(0, 0);
            },
        });
    }, 500),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Transactions</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Transactions">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permisions?.include('create-transactions')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                    :href="route('backoffice.transactions.create')"
                >
                    <font-awesome-icon icon="plus" />
                    <span class="hidden lg:inline">New</span>
                </Link>
                <Link
                    v-if="auth.permisions?.include('import-transactions')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                    :href="route('backoffice.transactions.import')"
                >
                    <font-awesome-icon icon="upload" />
                    <span class="hidden lg:inline">Import</span>
                </Link>
            </div>

            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Search</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Search for Payment" v-model="filters.query" />
                            </div>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Status</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.status">
                                    <option :value="undefined">All</option>
                                    <template v-for="status in statuses" :key="status.value">
                                        <option :value="status.value">{{ status.label }}</option>
                                    </template>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Method</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.method">
                                    <option :value="undefined">All</option>
                                    <template v-for="method in methods" :key="method.value">
                                        <option :value="method.value">{{ method.label }}</option>
                                    </template>
                                </select>
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.transactions.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </Link>

                                <a
                                    v-if="auth.permisions?.include('export-transactions')"
                                    class="btn btn-sm btn-primary btn-outline rounded-full"
                                    :href="route('backoffice.transactions.export', { ...filters })"
                                    download
                                >
                                    <font-awesome-icon icon="download" />
                                    <span class="hidden lg:inline">Export</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Reference</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Author</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="transactions.data.length">
                                            <tr v-for="(transaction, index) in transactions.data" :key="transaction.id">
                                                <td>{{ transactions.from + index }}</td>
                                                <td>{{ transaction.local_reference }}</td>
                                                <td>{{ transaction.amount }}</td>
                                                <td>{{ transaction.transaction_method ?? '-' }}</td>
                                                <td>{{ transaction.author?.name ?? '-' }}</td>
                                                <td>{{ transaction.status }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.transactions.show', [transaction.id])"> Details</Link>
                                                            </li>
                                                            <li>
                                                                <Link :href="route('backoffice.transactions.edit', [transaction.id])"> Edit</Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deleteTransaction(transaction)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="7" class="text-center">No transactions found.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <app-pagination :resource="transactions" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
