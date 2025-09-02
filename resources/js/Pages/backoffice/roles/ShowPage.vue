<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useRoles from '@/composables/useRoles';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    role: {
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
        title: 'Roles',
        href: route('backoffice.roles.index'),
    },
    {
        title: props.role.name,
        href: null,
    },
];

const { deleteRole } = useRoles();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Role Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Role Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('update-roles')"
                    :href="route('backoffice.roles.edit', [role.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions?.includes('delete-role')"
                    type="button"
                    @click="deleteRole(role)"
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
                                    <th class="w-1/5">ID</th>
                                    <td>{{ role.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ role.name }}</td>
                                </tr>
                                <tr>
                                    <th>Guard Name</th>
                                    <td>{{ role.guard_name }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(role.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(role.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Permissions</th>
                                    <td>
                                        <ul class="flex gap-3 flex-wrap">
                                            <li v-for="permission in role.permissions" :key="permission.id">
                                                {{ permission.name }}
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
