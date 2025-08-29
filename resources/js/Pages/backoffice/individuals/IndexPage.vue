<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useIndividuals from '@/composables/useIndividuals';
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
    individuals: {
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
        title: 'Individuals',
        href: null,
    },
];

const { deleteIndividual } = useIndividuals();

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(
            route('backoffice.individuals.index'),
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
        <title>Individuals</title>
        <meta name="description" content="Individuals Page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Individuals">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-individuals')"
                    :href="route('backoffice.individuals.create')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>New</span>
                </Link>
                <Link
                    v-if="auth.permissions.includes('import-individuals')"
                    :href="route('backoffice.individuals.import')"
                    class="btn btn-sm btn-outline btn-primary rounded-full"
                >
                    <Upload class="h-4 w-4" />
                    <span>Import</span>
                </Link>
            </div>

            <app-alert :feedback="feedback" />

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
                                <Link :href="route('backoffice.individuals.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                    <font-awesome-icon icon="times" />
                                    Reset
                                </Link>

                                <a
                                    v-if="auth.permissions.includes('export-individuals')"
                                    :href="route('backoffice.individuals.export', filters)"
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
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto pb-48">
                                <table class="table-xs table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Organization</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(individual, index) in props.individuals.data" :key="individual.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>
                                                <Link :href="route('backoffice.individuals.show', [individual.id])" class="text-primary">{{
                                                    individual.name
                                                }}</Link>
                                            </td>
                                            <td>{{ individual.organization?.name ?? '-' }}</td>
                                            <td>{{ individual.username ?? '-' }}</td>
                                            <td>{{ individual.email ?? '-' }}</td>
                                            <td>{{ individual.phone ?? '-' }}</td>
                                            <td>
                                                <div class="dropdown dropdown-end">
                                                    <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                        <font-awesome-icon icon="ellipsis-vertical" />
                                                    </div>
                                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                        <li>
                                                            <Link :href="route('backoffice.individuals.edit', [individual.id])"> Edit</Link>
                                                        </li>
                                                        <li><a href="#" role="button" @click="deleteIndividual(individual)">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <app-pagination :resource="individuals" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
