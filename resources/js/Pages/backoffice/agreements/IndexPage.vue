<script setup>
import AppAlert from '@/components/AppAlert.vue';
import AppPagination from '@/components/AppPagination.vue';
import useAgreements from '@/composables/useAgreements';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    agreements: {
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
        title: 'Agreements',
        href: null,
    },
];

const filters = reactive({
    ...props.params,
});

watch(
    filters,
    debounce((newFilters) => {
        router.get(route('backoffice.agreements.index'), newFilters);
    }, 500),
    { deep: true },
);

const { deleteAgreement } = useAgreements();
</script>

<template>
    <Head>
        <title>Agreements</title>
        <meta name="description" content="Manage agreements in the backoffice." />
    </Head>
    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Agreements">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions.includes('create-agreements')"
                    :href="route('backoffice.agreements.create')"
                    class="btn btn-sm btn-primary btn-outline rounded-full"
                >
                    <font-awesome-icon icon="plus" />
                    New
                </Link>
            </div>
        </div>

        <AppAlert :feedback="feedback" />

        <div class="grid grid-cols-12 items-start gap-4">
            <div class="top-0 col-span-12 lg:sticky lg:order-last lg:col-span-3">
                <div class="card bg-base-100">
                    <div class="card-body space-y-4">
                        <h2 class="test-xl font-bold">Filter</h2>
                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text">Query</span>
                            </label>
                            <input type="text" class="input input-bordered w-full" placeholder="Name" v-model="filters.query" />
                        </div>
                        <hr />
                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('backoffice.agreements.index')" class="btn btn-sm btn-primary btn-outline rounded-full">
                                <font-awesome-icon icon="times" />
                                Reset
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-9">
                <div class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <div class="overflow-x-auto pb-48">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Author</th>
                                        <th>Store</th>
                                        <th>Client</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="agreements.data?.length">
                                        <tr v-for="(agreement, index) in agreements.data" :key="agreement.id">
                                            <td>{{ agreements.from + index }}</td>
                                            <td>{{ agreement.author?.name }}</td>
                                            <td>{{ agreement.store?.name }}</td>
                                            <td>{{ agreement.client?.name }}</td>
                                            <td>{{ agreement.status }}</td>
                                            <td>
                                                <div class="dropdown dropdown-end">
                                                    <div tabindex="0" role="button" class="btn btn-sm btn-ghost m-1">
                                                        <font-awesome-icon icon="ellipsis-vertical" />
                                                    </div>
                                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                                                        <li>
                                                            <Link :href="route('backoffice.agreements.show', [agreement.id])"> Details</Link>
                                                        </li>
                                                        <li>
                                                            <Link :href="route('backoffice.agreements.edit', [agreement.id])"> Edit </Link>
                                                        </li>
                                                        <li><a href="#" role="button" @click="deleteAgreement(agreement)">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td colspan="6" class="text-center">No agreements found.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <AppPagination :resource="agreements" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
