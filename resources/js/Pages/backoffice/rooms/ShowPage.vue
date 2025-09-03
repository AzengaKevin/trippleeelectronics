<script setup>
import useBuildings from '@/composables/useBuildings';
import useDate from '@/composables/useDate';
import useRooms from '@/composables/useRooms';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    room: {
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
        title: 'Rooms',
        href: route('backoffice.rooms.index'),
    },
    {
        title: props.room.name,
        href: null,
    },
];

const { deleteBuilding } = useBuildings();
const { deleteRoom } = useRooms();
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
        <title>Room Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Room Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        v-if="auth.permissions.includes('update-rooms')"
                        :href="route('backoffice.rooms.edit', [room.id])"
                        class="btn btn-sm btn-info btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="edit" />
                        Edit
                    </Link>
                    <button
                        v-if="auth.permissions.includes('delete-rooms')"
                        type="button"
                        @click="deleteRoom(room)"
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
                                    <td>{{ room.id }}</td>
                                </tr>
                                <tr>
                                    <th>Building</th>
                                    <td>{{ room.building?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Room Type</th>
                                    <td>{{ room.room_type?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ room.name }}</td>
                                </tr>
                                <tr>
                                    <th>Code</th>
                                    <td>{{ room.code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Price</th>
                                    <td>{{ room.price ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Active</th>
                                    <td>{{ room.active ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(room.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(room.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
