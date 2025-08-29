<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    employee: {
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
        title: 'Employment',
        href: null,
    },
];

const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Employment Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Employment Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link :href="route('backoffice.employment.edit')" class="btn btn-sm btn-info btn-outline rounded-full">
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
            </div>

            <app-alert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <tbody>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ employee.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ employee.name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ employee.email }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ employee.phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Identification Number</th>
                                    <td>{{ employee.identification_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>KRA Pin</th>
                                    <td>{{ employee.kra_pin ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Department</th>
                                    <td>{{ employee.department ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Position</th>
                                    <td>{{ employee.position ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Hire Date</th>
                                    <td>{{ employee?.hire_date ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Documents</th>
                                    <td v-if="employee.media.length">
                                        <div class="overflow-x-flow">
                                            <table class="table-xs table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>name</th>
                                                        <th>File</th>
                                                        <th>URL</th>
                                                        <th>Mime</th>
                                                        <th>Size</th>
                                                        <th>Upload</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(media, index) in employee.media">
                                                        <td>{{ index + 1 }}</td>
                                                        <td>{{ media.name ?? '-' }}</td>
                                                        <td>{{ media.file_name ?? '-' }}</td>
                                                        <td>{{ media.url ?? '-' }}</td>
                                                        <td>{{ media.mime_type ?? '-' }}</td>
                                                        <td>{{ media.size ?? '-' }}</td>
                                                        <td>{{ formatDate(media.created_at) }}</td>
                                                        <td>
                                                            <div class="flex gap-2">
                                                                <a :href="media.url" target="_blank" class="btn btn-xs btn-primary btn-outline">
                                                                    <font-awesome-icon icon="eye" />
                                                                    <span>View</span>
                                                                </a>
                                                                <a :href="media.url" class="btn btn-xs btn-primary btn-outline" download>
                                                                    <font-awesome-icon icon="download" />
                                                                    <span>Download</span>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                    <td v-else>-</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(employee.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(employee.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
