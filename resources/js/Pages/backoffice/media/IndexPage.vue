<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import useMedia from '@/composables/useMedia';
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
    media: {
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
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Media',
        href: null,
    },
];

const { deleteMedia } = useMedia();
const { formatDate } = useDate();
const { showFeedbackSwal } = useSwal();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.media.index'), newFilters, { preserveState: true, replace: true });
    }, 300),
    { deep: true },
);

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
        <title>Media</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Media">
        <div class="space-y-4">
            <div class="grid grid-cols-12 items-start gap-2">
                <div class="col-span-12 lg:col-span-9">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-sm table">
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
                                        <template v-if="props.media.data.length">
                                            <tr v-for="(item, index) in props.media.data" :key="item.id">
                                                <td>{{ index + props.media.from }}</td>
                                                <td>
                                                    <Link :href="route('backoffice.media.show', [item.id])" class="text-primary">
                                                        {{ item.name }}
                                                    </Link>
                                                </td>
                                                <td>{{ item.file_name ?? '-' }}</td>
                                                <td>{{ item.url ?? '-' }}</td>
                                                <td>{{ item.mime_type ?? '-' }}</td>
                                                <td>{{ item.size ?? '-' }}</td>
                                                <td>{{ formatDate(item.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.media.edit', [item.id])"> Edit </Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deleteMedia(item)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="8" class="text-center">No items found.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <app-pagination :resource="media" />
                        </div>
                    </div>
                </div>
                <div class="top-0 order-first col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-[#87ceeb] shadow">
                        <div class="card-body space-y-4">
                            <h2 class="text-lg font-semibold">Filters</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Query</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Query" v-model="filters.query" />
                            </div>

                            <hr />

                            <div class="flex flex-wrap gap-3">
                                <Link type="button" class="btn btn-sm btn-primary btn-outline rounded-full" :href="route('backoffice.media.index')">
                                    <font-awesome-icon icon="times" />
                                    <span>Clear</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
