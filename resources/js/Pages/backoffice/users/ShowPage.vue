<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useUsers from '@/composables/useUsers';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    user: {
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
        title: 'Users',
        href: route('backoffice.users.index'),
    },
    {
        title: props.user.name,
        href: null,
    },
];

const { deleteUser, resetUserPassword } = useUsers();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>User Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="User Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('update-users')"
                    :href="route('backoffice.users.edit', [user.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions?.includes('update-users')"
                    type="button"
                    @click="resetUserPassword(user)"
                    class="btn btn-sm btn-accent btn-outline rounded-full"
                >
                    <font-awesome-icon icon="lock" />
                    Reset Password
                </button>
                <button
                    v-if="auth.permissions?.includes('delete-users')"
                    type="button"
                    @click="deleteUser(user)"
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
                                    <td>{{ user.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ user.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ user.name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ user.email }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ user.phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Roles</th>
                                    <td>{{ user.roles?.length ? user.roles.map((role) => role.name).join(', ') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Stores</th>
                                    <td>{{ user.stores?.length ? user.stores.map((store) => store.name).join(', ') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(user.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(user.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
