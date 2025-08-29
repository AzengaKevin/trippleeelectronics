<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useCarousels from '@/composables/useCarousels';
import useDate from '@/composables/useDate';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    carousel: {
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
        title: 'Carousels',
        href: route('backoffice.carousels.index'),
    },
    {
        title: props.carousel.title,
        href: null,
    },
];

const { deleteCarousel } = useCarousels();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Carousel Details</title>
        <meta name="description" content="Carousel details page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Carousel Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permisssions?.includes('update-carousels')"
                    :href="route('backoffice.carousels.edit', [carousel.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permisssions?.includes('delete-carousels')"
                    type="button"
                    @click="deleteCarousel(carousel)"
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
                                    <td>{{ carousel.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ carousel.id }}</td>
                                </tr>
                                <tr>
                                    <th>Title</th>
                                    <td>{{ carousel.title }}</td>
                                </tr>
                                <tr>
                                    <th>Slug</th>
                                    <td>{{ carousel.slug ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Image</th>
                                    <td>
                                        <img v-if="carousel.image_url" :src="carousel.image_url" width="120" :alt="carousel.name" />
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ carousel.description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Link</th>
                                    <td>{{ carousel?.link ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Orientation</th>
                                    <td>{{ carousel.orientation ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Position</th>
                                    <td>{{ carousel?.position ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(carousel.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(carousel.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
