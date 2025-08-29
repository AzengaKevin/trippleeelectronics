<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import useNotifications from '@/composables/useNotifications';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    notifications: {
        type: Object,
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
        title: 'Notifications',
        href: null,
    },
];

const { deleteNotification, markAsRead } = useNotifications();

const { formatDate } = useDate();

const filters = reactive({ ...props.params });

watch(
    () => filters,
    debounce((newFilters) => {
        router.get(route('backoffice.notifications.index'), newFilters);
    }, 300),
    { deep: true },
);
</script>

<template>
    <Head>
        <title>Notifications</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Notifications">
        <div class="space-y-4">
            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div>
                                <label class="flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" v-model="filters.with_read" class="checkbox checkbox-primary" />
                                    <span class="label-text">With Read</span>
                                </label>
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.notifications.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Message</th>
                                            <th>Created At</th>
                                            <th>Read At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="props.notifications.data.length">
                                            <tr v-for="(notification, index) in props.notifications.data" :key="notification.id">
                                                <td>{{ index + props.notifications.from }}</td>
                                                <td>{{ notification.data.title }}</td>
                                                <td>{{ notification.data.message }}</td>
                                                <td>{{ formatDate(notification.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                                <td>{{ notification.read_at ? formatDate(notification.read_at, 'YY-MM-DD HH:mm:ss') : '-' }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li v-if="!notification.read_at">
                                                                <a
                                                                    v-if="auth.permissions?.includes('update-notifications')"
                                                                    href="#"
                                                                    role="button"
                                                                    @click="markAsRead(notification)"
                                                                    >Mark As Read</a
                                                                >
                                                            </li>
                                                            <li>
                                                                <Link :href="notification.data.action_url"> Details </Link>
                                                            </li>
                                                            <li>
                                                                <a
                                                                    v-if="auth.permissions?.includes('delete-notifications')"
                                                                    href="#"
                                                                    role="button"
                                                                    @click="deleteNotification(notification)"
                                                                    >Delete</a
                                                                >
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="6" class="text-center">No notifications found.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="notifications" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
