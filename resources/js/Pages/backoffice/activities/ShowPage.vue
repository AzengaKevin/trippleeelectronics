<script setup>
import useActivities from '@/composables/useActivities';
import useDate from '@/composables/useDate';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';

import { Head } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    activity: {
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
        title: 'Activities',
        href: route('backoffice.activities.index'),
    },
    {
        title: `Activity #${props.activity.id}`,
        href: null,
    },
];

const { deleteActivity } = useActivities();
const { formatDate } = useDate();
const { showFeedbackSwal } = useSwal();

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
);
</script>

<template>
    <Head>
        <title>Activity Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Activity Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        v-if="auth.permissions.includes('delete-activities')"
                        type="button"
                        @click="deleteActivity(activity)"
                        class="btn btn-sm btn-error btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="trash-alt" />
                        Delete
                    </button>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <tbody>
                                <tr>
                                    <th>Activity ID</th>
                                    <td>{{ activity.id }}</td>
                                </tr>
                                <tr>
                                    <th>Log Name</th>
                                    <td>{{ activity.log_name }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ activity.description }}</td>
                                </tr>
                                <tr>
                                    <th>Subject</th>
                                    <td>
                                        <pre>
                                            {{ activity.subject }}
                                        </pre>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Causer</th>
                                    <td>{{ activity.causer?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Properties</th>
                                    <td>
                                        <pre>
                                            {{ activity.properties }}
                                        </pre>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(activity.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(activity.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
