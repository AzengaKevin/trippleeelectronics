<script setup>
import BrandsComponent from '@/components/BrandsComponent.vue';
import CarouselsComponent from '@/components/CarouselsComponent.vue';
import ProductHorizontalScrollComponent from '@/components/ProductHorizontalScrollComponent.vue';
import BaseLayout from '@/layouts/BaseLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
    tree_categories: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    carousels: {
        type: Array,
        required: true,
    },
    categories_with_products: {
        type: Array,
        required: true,
    },
    brands: {
        type: Array,
        required: true,
    },
    services: {
        type: Array,
        required: true,
    },
});

const appName = import.meta.env.VITE_APP_NAME;
</script>

<template>
    <Head>
        <title>Welcome</title>
    </Head>

    <base-layout :auth="auth" :settings="settings" :services="services" :categories="categories" :tree-categories="tree_categories">
        <carousels-component :carousels="carousels" />

        <div class="mt-4 space-y-4 px-4">
            <section v-for="category in categories_with_products" :key="category.id">
                <div class="space-y-3">
                    <div class="from-primary to-accent flex items-center justify-between bg-gradient-to-r p-2 font-medium text-white">
                        <h2 class="text-xl font-medium hover:border-b-2">
                            {{ category.name }}
                        </h2>
                        <Link :href="route('products.categories.index', { slug: category.slug })" class="text-primary-content hover:border-b-2"
                            >View More
                        </Link>
                    </div>

                    <product-horizontal-scroll-component :category="category" />
                </div>
            </section>

            <brands-component :brands="brands" />
        </div>
    </base-layout>
</template>
