<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import usePrice from '@/composables/usePrice';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';

import { Head, Link } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    period: {
        type: Object,
        required: true,
    },
    entries: {
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
    { title: 'Accounting Periods', href: route('backoffice.accounting.index') },
    { title: props.period.name, href: null },
];

const { showFeedbackSwal } = useSwal();

const { formatDate } = useDate();

const { formatPrice } = usePrice();

const filters = reactive({
    ...props.params,
});

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
        <title>Accounting Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Accounting Details">
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
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Amount</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="entries.data.length">
                                            <tr v-for="(entry, index) in entries.data" :key="entry.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>{{ entry.account.name ?? '-' }}</td>
                                                <td>{{ formatPrice(entry.amount) }}</td>
                                                <td>{{ formatDate(entry.created_at, 'YY-MM-DD') }}</td>
                                                <td>-</td>
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

                            <app-pagination :resource="entries" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
