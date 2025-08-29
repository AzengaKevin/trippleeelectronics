<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useItemCategories from '@/composables/useItemCategories';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    category: {
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
        title: 'Item Categories',
        href: route('backoffice.item-categories.index'),
    },
    {
        title: props.category.name,
        href: null,
    },
];

const { deleteItemCategory } = useItemCategories();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Item Category Details</title>
        <meta name="description" content="Item category details page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Item Category Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('update-item-categories')"
                    :href="route('backoffice.item-categories.edit', [category.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions.includes('delete-item-categories')"
                    type="button"
                    @click="deleteItemCategory(category)"
                    class="btn btn-sm btn-error btn-outline rounded-full"
                >
                    <font-awesome-icon icon="trash-alt" />
                    Delete
                </button>
            </div>

            <app-alert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <tbody>
                                <tr>
                                    <th>Author</th>
                                    <td>{{ category.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ category.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ category.name }}</td>
                                </tr>
                                <tr>
                                    <th>Image</th>
                                    <td>
                                        <img v-if="category.image_url" :src="category.image_url" width="120" :alt="category.name" />
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ category.description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Items Count</th>
                                    <td>{{ category.items_count_manual ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(category.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(category.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
