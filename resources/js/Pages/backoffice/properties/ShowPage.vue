<script setup>
import useDate from '@/composables/useDate';
import useProperties from '@/composables/useProperties';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    property: {
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
        title: 'Properties',
        href: route('backoffice.properties.index'),
    },
    {
        title: props.property.name,
        href: null,
    },
];

const { deleteProperty } = useProperties();
const { showFeedbackSwal } = useSwal();
const { formatDate } = useDate();

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
    {
        immediate: true,
    },
);
</script>

<template>
    <Head>
        <title>Property Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Property Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        v-if="auth.permissions.includes('update-properties')"
                        :href="route('backoffice.properties.edit', [property.id])"
                        class="btn btn-sm btn-info btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="edit" />
                        Edit
                    </Link>
                    <button
                        v-if="auth.permissions.includes('delete-properties')"
                        type="button"
                        @click="deleteProperty(property)"
                        class="btn btn-sm btn-error btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="trash-alt" />
                        Delete
                    </button>
                </div>
            </div>

            <div class="card bg-blue-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <tbody>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ property.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ property.name }}</td>
                                </tr>
                                <tr>
                                    <th>Code</th>
                                    <td>{{ property.code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ property.address ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Active</th>
                                    <td>{{ property.active ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(property.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(property.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
