<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useOrganizations from '@/composables/useOrganizations';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    organization: {
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
        title: 'Organizations',
        href: route('backoffice.organizations.index'),
    },
    {
        title: props.organization.name,
        href: null,
    },
];

const { deleteOrganization } = useOrganizations();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Organization Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Organization Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('update-organizations')"
                    :href="route('backoffice.organizations.edit', [organization.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions.includes('delete-organizations')"
                    type="button"
                    @click="deleteOrganization(organization)"
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
                                    <td>{{ organization.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ organization.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ organization.name }}</td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td>{{ organization.organization_category?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Image</th>
                                    <td>
                                        <img v-if="organization.image_url" :src="organization.image_url" width="120" :alt="organization.name" />
                                        <span v-else>No Image</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ organization.phone }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ organization.email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ organization.address ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>KRA PIN</th>
                                    <td>{{ organization.kra_pin ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(organization.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(organization.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
