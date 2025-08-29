<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useActivities from '@/composables/useActivities';
import useDate from '@/composables/useDate';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import debounce from 'lodash/debounce';

import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    activities: {
        type: Object,
        required: true,
    },
    causers: {
        type: Array,
        default: () => [],
    },
    params: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Activities', href: null },
];

const { showFeedbackSwal } = useSwal();

const { formatDate } = useDate();

const { deleteActivity } = useActivities();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.activities.index'), { ...newFilters }, { preserveState: true, replace: true });
    }, 500),
    { deep: true },
);

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
        <title>Activity Logs</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Activity Logs">
        <div class="space-y-4">
            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Query</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>

                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Causer</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.causer">
                                    <option :value="undefined">All</option>
                                    <template v-for="causer in props.causers" :key="causer.id">
                                        <option :value="causer.id">{{ causer.name }}</option>
                                    </template>
                                </select>
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.activities.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    Reset
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-xs table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Log</th>
                                            <th>Causer</th>
                                            <th>Description</th>
                                            <th>Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="activities.data.length">
                                            <tr v-for="(activity, index) in activities.data" :key="activity.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>{{ activity.log_name ?? '-' }}</td>
                                                <td>
                                                    <Link
                                                        v-if="activity.causer"
                                                        class="link link-primary"
                                                        :href="route('backoffice.users.show', [activity.causer_id])"
                                                    >
                                                        {{ activity.causer.name }}
                                                    </Link>
                                                    <span v-else>System</span>
                                                </td>
                                                <td>{{ activity.description ?? '-' }}</td>
                                                <td>{{ formatDate(activity.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.activities.show', [activity.id])"> Details</Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deleteActivity(activity)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="6" class="text-center">No orders found.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="activities" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
