<script setup>
import AppPagination from '@/components/AppPagination.vue';
import ProductCard from '@/components/ProductCard.vue';
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
    products: {
        type: Object,
        required: true,
    },
});
</script>
<template>
    <Head>
        <title>Products</title>
    </Head>
    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main class="px-4">
            <div class="space-y-4 pt-4">
                <h1 class="text-3xl font-semibold">Products</h1>
                <div class="mt-4 grid w-full grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 2xl:grid-cols-8">
                    <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
                </div>

                <app-pagination :resource="products" />
            </div>
        </main>
    </base-layout>
</template>
