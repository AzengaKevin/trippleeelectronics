<script setup>
import AppPagination from '@/components/AppPagination.vue';
import useBuildings from '@/composables/useBuildings';
import useDate from '@/composables/useDate';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import ImportBuildingsDialog from '@/Pages/backoffice/buildings/ImportBuildingsDialog.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { Download, Plus, Upload } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    properties: {
        type: Array,
        required: true,
    },
    buildings: {
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
        title: 'Buildings',
        href: null,
    },
];

const { formatDate } = useDate();

const { deleteBuilding } = useBuildings();

const { showFeedbackSwal } = useSwal();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.buildings.index'),
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

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback?.type && newFeedback?.message) {
            showFeedbackSwal(newFeedback);
        }
    },
    { immediate: true },
);
</script>
<template>
    <Head>
        <title>Buildings</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Buildings">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-buildings')"
                    :href="route('backoffice.buildings.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <button
                    v-if="auth.permissions.includes('import-buildings')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                    onclick="importBuildingsDialog.showModal()"
                >
                    <Upload class="h-4 w-4" />
                    <span>Import</span>
                </button>
            </div>

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <h2 class="test-xl font-bold">Filter</h2>
                            <div class="space-y-2">
                                <label class="label">
                                    <span class="label-text">Name</span>
                                </label>
                                <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                            </div>
                            <hr />
                            <div class="flex flex-wrap gap-3">
                                <Link :href="route('backoffice.buildings.index')" class="btn btn-sm btn-outline btn-warning rounded-full">
                                    <font-awesome-icon icon="times" />
                                    <span>Reset</span>
                                </Link>
                                <a
                                    v-if="auth.permissions.includes('export-buildings')"
                                    :href="route('backoffice.buildings.export', filters)"
                                    class="btn btn-sm btn-outline btn-primary rounded-full"
                                >
                                    <Download class="h-4 w-4" />
                                    <span>Export</span>
                                </a>
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
                                            <th>Created At</th>
                                            <th>Updated At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="props.buildings.data.length">
                                            <tr v-for="(building, index) in props.buildings.data" :key="building.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>
                                                    <Link :href="route('backoffice.buildings.show', [building.id])" class="text-primary">{{
                                                        building.name
                                                    }}</Link>
                                                </td>
                                                <td>{{ formatDate(building.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                                <td>{{ formatDate(building.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                                <td>
                                                    <div class="dropdown dropdown-end">
                                                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                            <font-awesome-icon icon="ellipsis-vertical" />
                                                        </div>
                                                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                            <li>
                                                                <Link :href="route('backoffice.buildings.edit', [building.id])"> Edit</Link>
                                                            </li>
                                                            <li><a href="#" role="button" @click="deleteBuilding(building)">Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="5" class="text-center">No buildings found</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="buildings" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>

    <teleport to="body">
        <ImportBuildingsDialog />
    </teleport>
</template>
