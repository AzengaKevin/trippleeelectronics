<script setup>
import AppPagination from '@/components/AppPagination.vue';
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
    periods: {
        type: Object,
        required: true,
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
    { title: 'Accounting Periods', href: null },
];

const { showFeedbackSwal } = useSwal();

const { formatDate } = useDate();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.accounting.index'), { ...newFilters }, { preserveState: true, replace: true });
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
        <title>Accounting Periods</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Accounting Periods">
        <div class="space-y-4">
            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Start Date</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Start Date" v-model="filters.start_date" />
                            </div>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">End Date</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="End Date" v-model="filters.end_date" />
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.accounting.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
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
                                            <th>Name</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="periods.data.length">
                                            <tr v-for="(period, index) in periods.data" :key="period.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>
                                                    <Link :href="route('backoffice.accounting.show', period.id)" class="link link-primary">{{
                                                        period.name ?? '-'
                                                    }}</Link>
                                                </td>
                                                <td>{{ formatDate(period.start_date, 'YY-MM-DD') }}</td>
                                                <td>{{ formatDate(period.end_date, 'YY-MM-DD') }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.accounting.show', period.id)"> Details </Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deletePeriod(period)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="5" class="text-center">No periods found.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="periods" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
