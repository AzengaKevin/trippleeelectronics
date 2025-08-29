<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import usePaymentMethods from '@/composables/usePaymentMethods';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import ImportDialog from '@/Pages/backoffice/payment-methods/ImportDialog.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    methods: {
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
        title: 'Payment Methods',
        href: null,
    },
];

const { formatDate } = useDate();
const { deletePaymentMethod } = usePaymentMethods();

const filters = reactive({ ...props.params });

watch(
    () => filters,
    debounce((newFilters) => {
        router.get(route('backoffice.payment-methods.index'), newFilters);
    }, 500),
    { deep: true },
);
</script>
<template>
    <Head>
        <title>Payment Methods</title>
        <meta name="description" content="Payment Methods Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Payment Methods">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('create-payment-methods')"
                    :href="route('backoffice.payment-methods.create')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <font-awesome-icon icon="plus" />
                    <span>New</span>
                </Link>

                <button
                    v-if="auth.permissions?.includes('import-payment-methods')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                    onclick="importPaymentMethodsDialog.showModal()"
                >
                    <font-awesome-icon icon="file-import" />
                    <span>Import</span>
                </button>
            </div>
        </div>

        <app-alert :feedback="feedback" />

        <div class="grid grid-cols-12 items-start gap-4">
            <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                <div class="card bg-base-100">
                    <div class="card-body space-y-4">
                        <h2 class="test-xl font-bold">Filter</h2>
                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text">Name</span>
                            </label>
                            <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                        </div>
                        <hr />
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('backoffice.payment-methods.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                <font-awesome-icon icon="times" />
                                <span>Reset</span>
                            </Link>

                            <a
                                v-if="auth.permissions?.includes('export-permissions')"
                                :href="route('backoffice.payment-methods.export', filters)"
                                class="btn btn-sm btn-outline btn-primary rounded-full"
                            >
                                <font-awesome-icon icon="file-export" />
                                <span>Export</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-9">
                <div class="card bg-base-100">
                    <div class="card-body lg-p-6 p-2 sm:p-4">
                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(method, index) in methods.data" :key="method.id">
                                        <td>{{ index + methods.from }}</td>
                                        <td>
                                            <Link :href="route('backoffice.payment-methods.show', method.id)" class="text-primary hover:underline">{{
                                                method.name
                                            }}</Link>
                                        </td>
                                        <td>{{ method.description ?? '-' }}</td>
                                        <td>{{ formatDate(method.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                        <td>
                                            <div class="dropdown dropdown-end">
                                                <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                    <font-awesome-icon icon="ellipsis-vertical" />
                                                </div>
                                                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                    <li>
                                                        <Link
                                                            v-if="auth.permissions?.includes('update-payment-methods')"
                                                            :href="route('backoffice.payment-methods.edit', [method.id])"
                                                        >
                                                            Edit</Link
                                                        >
                                                    </li>
                                                    <li>
                                                        <a
                                                            v-if="auth.permissions?.includes('delete-payment-methods')"
                                                            role="button"
                                                            @click="deletePaymentMethod(method)"
                                                            href="#"
                                                            >Delete</a
                                                        >
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <app-pagination :resource="methods" />
                    </div>
                </div>
            </div>
        </div>

        <teleport to="body">
            <ImportDialog />
        </teleport>
    </BackofficeLayout>
</template>
