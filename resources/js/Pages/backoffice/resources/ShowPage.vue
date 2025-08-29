<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useResources from '@/composables/useResources';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    resource: {
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
        title: 'Resources',
        href: route('backoffice.resources.index'),
    },
    {
        title: props.resource.name,
        href: null,
    },
];

const { deleteResource } = useResources();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Resource Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Resource Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('update-resources')"
                    :href="route('backoffice.resources.edit', [resource.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions?.includes('delete-resources')"
                    type="button"
                    @click="deleteResource(resource)"
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
                                    <td>{{ resource.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ resource.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ resource.name }}</td>
                                </tr>
                                <tr>
                                    <th>Route Name</th>
                                    <td>{{ resource.route_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Icon</th>
                                    <td>{{ resource.icon ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Order</th>
                                    <td>{{ resource.order ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ resource.description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Is Active</th>
                                    <td>{{ resource.is_active ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <th>Count</th>
                                    <td>{{ resource.count ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Required Permission</th>
                                    <td>{{ resource.required_permission ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Morph Class</th>
                                    <td>{{ resource.morph_class ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(resource.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(resource.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
