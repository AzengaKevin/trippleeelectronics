<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useCarousels from '@/composables/useCarousels';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { Download, Plus, Upload } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    carousels: {
        type: Object,
        required: true,
    },
    positions: {
        type: Array,
        required: true,
    },
    orientations: {
        type: Array,
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
        title: 'Carousels',
        href: null,
    },
];

const { deleteCarousel } = useCarousels();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.carousels.index'),
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
        <title>Carousels</title>
        <meta name="description" content="Carousel List Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Carousels">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('create-carousels')"
                    :href="route('backoffice.carousels.create')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span class="hidden lg:inline">New</span>
                </Link>
                <Link
                    v-if="auth.permissions?.includes('import-carousels')"
                    :href="route('backoffice.carousels.import')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span class="hidden lg:inline">Import</span>
                </Link>
            </div>

            <AppAlert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="sticky top-0 col-span-12 lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <h2 class="card-title">Filters</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Search</span>
                                </label>
                                <input type="search" class="input input-bordered w-full" placeholder="Search" v-model="filters.query" />
                            </div>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Position</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.position">
                                    <option :value="undefined">All</option>
                                    <option v-for="position in positions" :key="position.value" :value="position.value">
                                        {{ position.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Orientation</span>
                                </label>
                                <select class="select select-bordered w-full" v-model="filters.orientation">
                                    <option :value="undefined">All</option>
                                    <option v-for="orientation in orientations" :key="orientation.value" :value="orientation.value">
                                        {{ orientation.label }}
                                    </option>
                                </select>
                            </div>

                            <hr />

                            <div class="flex flex-wrap gap-3">
                                <Links :href="route('backoffice.carousels.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </Links>

                                <a
                                    v-if="auth.permissions?.includes('export-carousels')"
                                    :href="route('backoffice.carousels.export')"
                                    class="btn btn-sm btn-primary btn-outline rounded-full"
                                    download
                                >
                                    <Download class="h-4 w-4" />
                                    <span class="hidden lg:inline">Export</span>
                                </a>
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
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Position</th>
                                            <th>Link</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="carousels.data.length">
                                            <tr v-for="(carousel, index) in carousels.data" :key="carousel.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>
                                                    <img
                                                        v-if="carousel.image_url"
                                                        :src="carousel.image_url"
                                                        class="h-12 w-12 object-cover"
                                                        :alt="carousel.title"
                                                    />
                                                    <span v-else>-</span>
                                                </td>
                                                <td>
                                                    <Link :href="route('backoffice.carousels.show', [carousel.id])" class="text-primary">
                                                        {{ carousel.title }}
                                                    </Link>
                                                </td>
                                                <td>{{ carousel.position ?? '-' }}</td>
                                                <td>{{ carousel.link ?? '-' }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li v-if="auth.permissions?.includes('update-carousels')">
                                                                <Link :href="route('backoffice.carousels.edit', [carousel.id])"> Edit</Link>
                                                            </li>
                                                            <li v-if="auth.permissions?.includes('delete-carousels')">
                                                                <a href="#" @click="deleteCarousel(carousel)">Delete</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="6">No carousel results.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <app-pagination :resource="carousels" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
