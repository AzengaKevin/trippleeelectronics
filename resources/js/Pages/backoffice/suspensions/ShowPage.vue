<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useSuspensions from '@/composables/useSuspensions';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    suspension: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        required: false,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Suspensions', href: route('backoffice.suspensions.index') },
    { title: `# ${props.suspension.id}`, href: null },
];

const { deleteSuspension } = useSuspensions();
const { formatDate } = useDate();
</script>
<template>
    <Head>
        <title>Suspension Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Suspension Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        v-if="auth.permissions.includes('update-contracts')"
                        :href="route('backoffice.suspensions.edit', [suspension.id])"
                        class="btn btn-sm btn-info btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="edit" />
                        Edit
                    </Link>
                    <button
                        v-if="auth.permissions.includes('delete-contracts')"
                        type="button"
                        @click="deleteSuspension(contract)"
                        class="btn btn-sm btn-error btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="trash-alt" />
                        Delete
                    </button>
                </div>
            </div>

            <app-alert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <tbody>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ suspension.id }}</td>
                                </tr>
                                <tr>
                                    <th>Employee</th>
                                    <td>{{ suspension.employee?.name }}</td>
                                </tr>
                                <tr>
                                    <th>From</th>
                                    <td>{{ suspension.from ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>To</th>
                                    <td>{{ suspension.to ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Reason</th>
                                    <td>{{ suspension.reason ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(suspension.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(suspension.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
