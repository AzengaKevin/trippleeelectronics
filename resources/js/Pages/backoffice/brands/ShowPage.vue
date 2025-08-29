<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useBrands from '@/composables/useBrands';
import useDate from '@/composables/useDate';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    brand: {
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
        title: 'Brands',
        href: route('backoffice.brands.index'),
    },
    {
        title: props.brand.name,
        href: null,
    },
];

const { deleteBrand } = useBrands();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Brand Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Brand Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('update-brands')"
                    :href="route('backoffice.brands.edit', [brand.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions.includes('delete-brands')"
                    type="button"
                    @click="deleteBrand(brand)"
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
                                    <td>{{ brand.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ brand.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ brand.name }}</td>
                                </tr>
                                <tr>
                                    <th>Image</th>
                                    <td>
                                        <img v-if="brand.image_url" width="120" :src="brand.image_url" :alt="brand.name" />
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Slug</th>
                                    <td>{{ brand.slug }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ brand.description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Items Count</th>
                                    <td>{{ brand.items_count_manual ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(brand.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(brand.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
