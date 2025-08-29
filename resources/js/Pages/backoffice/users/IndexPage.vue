<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import useUsers from '@/composables/useUsers';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { Download, Plus, Upload } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    users: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    stores: {
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
        title: 'Users',
        href: null,
    },
];

const { deleteUser, resetUserPassword } = useUsers();

const { formatDate } = useDate();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.users.index'),
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
        <title>Users</title>
        <meta name="description" content="Users page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Users">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('create-users')"
                    :href="route('backoffice.users.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span class="hidden lg:inline">New</span>
                </Link>
                <Link
                    v-if="auth.permissions?.includes('import-users')"
                    :href="route('backoffice.users.import')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span class="hidden lg:inline">Import</span>
                </Link>
            </div>
            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-2">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Name</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>
                            <div class="space-y-2">
                                <label for="role" class="label"><span class="label-text">Role</span></label>
                                <select v-model="filters.role" id="role" class="select select-bordered w-full">
                                    <option :value="undefined">All</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="role" class="label"><span class="label-text">Store</span></label>
                                <select v-model="filters.store" id="store" class="select select-bordered w-full">
                                    <option :value="undefined">All</option>
                                    <option v-for="store in stores" :key="store.id" :value="store.id">
                                        {{ store.name }}
                                    </option>
                                </select>
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.users.index')" class="btn btn-sm btn-warning btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </Link>

                                <a
                                    v-if="auth.permissions?.includes('export-users')"
                                    :href="route('backoffice.users.export')"
                                    class="btn btn-sm btn-outline btn-primary rounded-full"
                                    download
                                >
                                    <Download class="h-4 w-4" />
                                    <span class="hidden lg:inline">Export</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-sm table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Roles</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="users.data.length">
                                            <tr v-for="(user, index) in users.data" :key="user.id">
                                                <td>{{ users.from + index }}</td>
                                                <td>
                                                    <Link :href="route('backoffice.users.show', [user.id])" class="text-primary">{{
                                                        user.name
                                                    }}</Link>
                                                </td>
                                                <td>{{ user.email }}</td>
                                                <td>{{ user.phone ?? '-' }}</td>
                                                <td>{{ user.roles?.map((r) => r.name).join(', ') }}</td>
                                                <td>{{ formatDate(user.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.users.show', [user.id])"> Details</Link>
                                                            </li>
                                                            <li v-if="auth.permissions?.includes('update-users')">
                                                                <Link :href="route('backoffice.users.edit', [user.id])"> Edit </Link>
                                                            </li>
                                                            <li>
                                                                <a
                                                                    v-if="auth.permissions?.includes('delete-users')"
                                                                    href="#"
                                                                    role="button"
                                                                    @click="deleteUser(user)"
                                                                    >Delete</a
                                                                >
                                                            </li>
                                                            <li>
                                                                <a
                                                                    v-if="auth.permissions?.includes('update-users')"
                                                                    href="#"
                                                                    role="button"
                                                                    @click="resetUserPassword(user)"
                                                                    >Reset Password</a
                                                                >
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="7">No users found</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="users" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
