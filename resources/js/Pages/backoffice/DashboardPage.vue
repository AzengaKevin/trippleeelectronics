<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head>
        <title>Dashboard</title>
    </Head>

    <BackofficeLayout>
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <template v-if="auth.resources?.length">
                    <Link
                        v-for="resource in auth.resources"
                        :href="route(resource.route_name)"
                        class="card bg-base-100 shadow transition hover:shadow-md"
                    >
                        <div class="card-body flex flex-row items-center">
                            <font-awesome-icon :icon="resource.icon" class="text-primary text-4xl" />
                            <div class="ml-4">
                                <h2 class="card-title text-gray-700">{{ resource.name }}</h2>
                                <p class="text-gray-500">{{ resource.description }}</p>
                            </div>
                        </div>
                    </Link>
                </template>
                <template v-else>
                    <div class="col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-4">
                        <div class="card bg-base-100 shadow">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <h2 class="card-title">No Resources Results</h2>
                                <p class="text-gray-500">No resource matches the search query.</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </BackofficeLayout>
</template>
