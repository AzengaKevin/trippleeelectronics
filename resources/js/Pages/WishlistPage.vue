<script setup>
import usePrice from '@/composables/usePrice';
import BaseLayout from '@/layouts/BaseLayout.vue';
import { useCartStore } from '@/stores/cart';
import { useWishListStore } from '@/stores/wishlist';
import { Head, Link } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

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

const appName = import.meta.env.VITE_APP_NAME;

const wishListStore = useWishListStore();

const cartStore = useCartStore();

const { items } = storeToRefs(wishListStore);

const { toggleItemWishListPresence } = wishListStore;
const { addItemToCart } = cartStore;

const { formatPrice } = usePrice();

const createAPlaceholderImage = (text) => {
    const url = new URL('https://placehold.co/192x192');
    url.searchParams.append('text', text);
    return url.toString();
};

const processedItems = computed(() => {
    return items.value?.map((item) => {
        item.image_url = item.image_url ?? createAPlaceholderImage(item.name);
        return item;
    });
});
</script>
<template>
    <Head>
        <title>Wish List</title>
    </Head>
    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main class="container mx-auto max-w-6xl space-y-12 px-4 py-12">
            <div class="space-y-3">
                <h1 class="text-primary text-4xl font-bold">Wish List</h1>
                <div class="divider divider-primary w-24"></div>
                <p class="text-lg">Your trusted partner for comprehensive IT solutions since 2018</p>
            </div>
            <div v-if="processedItems?.length" class="card bg-base-100">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <ul class="mt-4">
                        <li v-for="item in processedItems" :key="item.id" class="border-t-primary flex border-t py-6">
                            <div class="relative h-24 w-24 overflow-hidden sm:h-48 sm:w-48">
                                <img
                                    :alt="item.name"
                                    loading="lazy"
                                    class="absolute inset-0 h-full w-full object-contain object-center text-transparent"
                                    :src="item.image_url"
                                />
                            </div>
                            <div class="relative ml-4 flex flex-1 justify-between sm:ml-6 sm:pr-12">
                                <div>
                                    <div class="flex flex-col gap-y-2">
                                        <p class="text-lg font-semibold text-neutral-950">
                                            {{ item.name }}
                                        </p>
                                        <div class="flex items-center gap-x-2">
                                            <h3 class="font-semibold">In Stock:</h3>
                                            <div v-if="item.stock_quantity > 0" class="flex items-center gap-x-2">
                                                <span>Yes</span>
                                                <span class="text-green-500">
                                                    <font-awesome-icon :icon="['regular', 'circle-check']" />
                                                </span>
                                            </div>
                                            <div v-else class="flex items-center gap-x-2">
                                                <span>Limited</span>
                                                <span class="text-amber-500">
                                                    <font-awesome-icon icon="circle-info" />
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-2">
                                            <h3 class="font-semibold">Unit Price:</h3>
                                            <div class="text-brand-100 flex flex-wrap items-center gap-x-1 text-base">
                                                <div class="font-semibold">{{ formatPrice(item.selling_price) }}</div>
                                                <span class="text-sm text-neutral-500">(VAT Exc)</span>
                                            </div>
                                        </div>
                                        <div class="my-2 flex flex-wrap items-center gap-x-4 gap-y-6 text-sm">
                                            <div class="absolute top-0 right-0">
                                                <button type="button" @click="toggleItemWishListPresence(item)" class="btn btn-circle btn-ghost">
                                                    <font-awesome-icon icon="times" />
                                                </button>
                                            </div>
                                            <button
                                                class="bg-accent flex w-auto items-center gap-x-2 border-transparent px-4 py-2 font-semibold text-white transition hover:opacity-75"
                                            >
                                                <span>Buy Now</span>
                                                <font-awesome-icon icon="credit-card" />
                                            </button>
                                            <button
                                                type="button"
                                                @click="addItemToCart(item)"
                                                class="bg-primary flex w-auto items-center gap-x-2 border-transparent px-4 py-2 font-semibold text-white transition hover:opacity-75"
                                            >
                                                <span>Add to Cart</span>
                                                <font-awesome-icon icon="shopping-cart" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div v-else class="flex flex-col items-center justify-center gap-y-4 px-4 py-6 text-center lg:py-12">
                <div class="text-accent border-accent flex h-24 w-24 items-center justify-center rounded-full border">
                    <font-awesome-icon icon="heart-circle-plus" size="2x" />
                </div>
                <p class="text-lg font-semibold">Your wishlist is empty!</p>
                <p>Browse our products to discover amazing products for your digital collection.</p>
                <Link class="bg-primary px-3 py-2 text-white hover:shadow-lg" :href="route('products.index')">Shop</Link>
            </div>
        </main>
    </base-layout>
</template>
