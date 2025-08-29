<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    threads: {
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
        title: 'Messages',
        href: null,
    },
];

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.messages.index'),
            {
                ...newFilters,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 500),
    { deep: true },
);
</script>
<template>
    <Head>
        <title>Messages</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Messages">
        <div class="space-y-4">
            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-2">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Name</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.messages.index')" class="btn btn-sm btn-warning btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100">
                        <div class="card-body">
                            <ul>
                                <li v-for="thread in props.threads.data" :key="thread.id" class="border-b border-slate-300">
                                    <Link
                                        :href="route('backoffice.messages.show', { thread: thread.id })"
                                        class="flex items-center justify-between gap-2 p-3"
                                    >
                                        <span class="font-semibold">{{ thread.name }}</span>
                                        <span class="badge bg-[#1bbe49]">{{ thread.unread_messages_count }}</span>
                                    </Link>
                                </li>
                            </ul>

                            <app-pagination :resource="props.threads" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
