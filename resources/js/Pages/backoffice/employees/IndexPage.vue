<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useEmployees from '@/composables/useEmployees';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    employees: {
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
        title: 'Employees',
        href: null,
    },
];

const { deleteEmployee, suspendEmployee } = useEmployees();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.employees.index'),
            {
                ...newFilters,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 500),
    { deep: true },
);
</script>
<template>
    <Head>
        <title>Employees</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Employees">
        <div class="space-y-4">
            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Query</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.employees.index')" class="btn btn-sm btn-warning btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-sm table w-full">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Position</th>
                                            <th>Hire Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="employees.data.length">
                                            <tr v-for="(employee, index) in employees.data" :key="employee.id">
                                                <td>{{ employees.from + index }}</td>
                                                <td>
                                                    <Link :href="route('backoffice.employees.show', [employee.id])" class="text-primary">{{
                                                        employee.name
                                                    }}</Link>
                                                </td>
                                                <td>{{ employee.email }}</td>
                                                <td>{{ employee.phone ?? '-' }}</td>
                                                <td>{{ employee.position ?? '-' }}</td>
                                                <td>{{ employee.hire_date ?? '-' }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.employees.show', [employee.id])"> Details</Link>
                                                            </li>
                                                            <li v-if="auth.permissions?.includes('update-employees')">
                                                                <Link :href="route('backoffice.employees.edit', [employee.id])"> Edit </Link>
                                                            </li>
                                                            <li>
                                                                <a
                                                                    v-if="auth.permissions?.includes('update-employees')"
                                                                    href="#"
                                                                    role="button"
                                                                    @click="suspendEmployee(employee)"
                                                                    >Suspend</a
                                                                >
                                                            </li>
                                                            <li>
                                                                <a
                                                                    v-if="auth.permissions?.includes('delete-employees')"
                                                                    href="#"
                                                                    role="button"
                                                                    @click="deleteEmployee(employee)"
                                                                    >Delete</a
                                                                >
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="7">No employees found</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="employees" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
