<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useContracts from '@/composables/useContracts';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    contracts: {
        type: Object,
        required: true,
    },
    employees: {
        type: Array,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    types: {
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
        title: 'Contracts',
        href: null,
    },
];

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.contracts.index'), newFilters);
    }, 500),
    { deep: true },
);

const { deleteContract } = useContracts();
</script>

<template>
    <Head>
        <title>Contracts</title>
    </Head>
    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Contracts">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-contracts')"
                    :href="route('backoffice.contracts.create')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <font-awesome-icon icon="plus" />
                    New
                </Link>
            </div>
        </div>

        <AppAlert :feedback="feedback" />

        <div class="grid grid-cols-12 items-start gap-4">
            <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                <div class="card bg-base-100">
                    <div class="card-body space-y-4">
                        <h2 class="test-xl font-bold">Filter</h2>

                        <div v-if="auth.user.roles.map((r) => r.name).includes('admin')" class="space-y-2">
                            <label class="label">
                                <span class="label-text">Employee</span>
                            </label>
                            <select class="select select-bordered w-full" v-model="filters.employee">
                                <option :value="undefined">All</option>
                                <template v-for="employee in props.employees" :key="employee.id">
                                    <option :value="employee.id">{{ employee.name }}</option>
                                </template>
                            </select>
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
                                <span class="label-text">Type</span>
                            </label>
                            <select class="select select-bordered w-full" v-model="filters.type">
                                <option :value="undefined">All</option>
                                <template v-for="t in props.types" :key="t.value">
                                    <option :value="t.value">{{ t.label }}</option>
                                </template>
                            </select>
                        </div>
                        <hr />
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('backoffice.contracts.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                <font-awesome-icon icon="times" />
                                Reset
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-9">
                <div class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <div class="overflow-x-auto pb-48">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Contact Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Salary</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="contracts.data?.length">
                                        <tr v-for="(contract, index) in contracts.data" :key="contract.id">
                                            <td>{{ contracts.from + index }}</td>
                                            <td>{{ contract.employee?.name }}</td>
                                            <td>{{ contract.contract_type ?? '-' }}</td>
                                            <td>{{ contract.start_date ?? '-' }}</td>
                                            <td>{{ contract.end_date ?? '-' }}</td>
                                            <td>{{ contract.salary ?? '-' }}</td>
                                            <td>{{ contract.status }}</td>
                                            <td>
                                                <div class="dropdown dropdown-end">
                                                    <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                        <font-awesome-icon icon="ellipsis-vertical" />
                                                    </div>
                                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                        <li>
                                                            <Link :href="route('backoffice.contracts.show', [contract.id])"> Details</Link>
                                                        </li>
                                                        <li>
                                                            <Link :href="route('backoffice.contracts.edit', [contract.id])"> Edit </Link>
                                                        </li>
                                                        <li><a href="#" role="button" @click="deleteContract(contract)">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td colspan="8" class="text-center">No contracts found.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <AppPagination :resource="contracts" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
