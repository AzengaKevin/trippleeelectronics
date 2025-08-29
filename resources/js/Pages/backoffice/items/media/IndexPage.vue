<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useDate from '@/composables/useDate';
import useItems from '@/composables/useItems';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    item: {
        type: Object,
        required: true,
    },
    media: {
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
        title: 'Items',
        href: route('backoffice.items.index'),
    },
    {
        title: props.item.name,
        href: route('backoffice.items.show', [props.item.id]),
    },
    {
        title: 'Media',
        href: null,
    },
];

const { deleteItem } = useItems();
const { showFeedbackSwal } = useSwal();
const { formatDate } = useDate();

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
    { immediate: true },
);
</script>

<template>
    <Head>
        <title>Item Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Item Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('update-items')"
                    :href="route('backoffice.items.edit', [item.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <Link
                    v-if="auth.permissions.includes('browse-items')"
                    :href="route('backoffice.items.media.index', [item.id])"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <font-awesome-icon icon="image" />
                    Media
                </Link>
                <button
                    v-if="auth.permissions.includes('delete-items')"
                    type="button"
                    @click="deleteItem(item)"
                    class="btn btn-sm btn-error btn-outline rounded-full"
                >
                    <font-awesome-icon icon="trash-alt" />
                    Delete
                </button>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-flow pb-48">
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
                                    <tr v-for="(m, index) in props.media.data">
                                        <td>{{ index + props.media.from }}</td>
                                        <td>{{ m.name ?? '-' }}</td>
                                        <td>{{ m.file_name ?? '-' }}</td>
                                        <td>{{ m.url ?? '-' }}</td>
                                        <td>{{ m.mime_type ?? '-' }}</td>
                                        <td>{{ m.size ?? '-' }}</td>
                                        <td>{{ formatDate(m.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a :href="m.url" target="_blank" class="btn btn-sm btn-primary btn-outline rounded-full">
                                                    <font-awesome-icon icon="eye" />
                                                    <span>View</span>
                                                </a>
                                                <a :href="m.url" class="btn btn-sm btn-primary btn-outline rounded-full" download>
                                                    <font-awesome-icon icon="download" />
                                                    <span>Download</span>
                                                </a>
                                                <Link
                                                    as="button"
                                                    method="DELETE"
                                                    :href="route('backoffice.items.media.destroy', [item.id, m.id])"
                                                    class="btn btn-sm btn-error btn-outline rounded-full"
                                                >
                                                    <font-awesome-icon icon="trash" />
                                                    <span>Delete</span>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr>
                                        <td colspan="8">No media has been uploaded for the item yet.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <app-pagination :resource="props.media" />
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
