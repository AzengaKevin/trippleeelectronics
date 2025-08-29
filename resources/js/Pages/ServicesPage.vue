<script setup>
import BaseLayout from '@/layouts/BaseLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    treeCategories: {
        type: Array,
        required: true,
    },
    services: {
        type: Array,
        required: true,
    },
});
</script>
<template>
    <Head>
        <title>Services</title>
    </Head>

    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main class="container mx-auto max-w-6xl space-y-12 px-4 py-12">
            <div class="space-y-3">
                <h1 class="text-primary text-4xl font-bold">Services</h1>
                <div class="divider divider-primary w-24"></div>
                <p class="text-lg">Your trusted partner for comprehensive IT solutions since 2018</p>
            </div>

            <section class="py-6">
                <div v-if="services?.length" class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div
                        v-for="service in services"
                        :key="service.id"
                        class="card card-md bg-base-100 shadow-sm transition-shadow duration-300 hover:shadow-lg"
                    >
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <h2 class="card-title">{{ service.title }}</h2>
                            <p>{{ service.description }}</p>
                            <div class="card-actions justify-end"></div>
                        </div>
                    </div>
                </div>
                <p v-else>No services have been listed yet!</p>
            </section>
        </main>
    </base-layout>
</template>
