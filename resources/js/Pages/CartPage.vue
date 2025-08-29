<script setup>
import usePrice from '@/composables/usePrice';
import BaseLayout from '@/layouts/BaseLayout.vue';
import { useCartStore } from '@/stores/cart';
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

const cartStore = useCartStore();

const { incrementCartItemQty, decrementCartItemQty, clearCart } = cartStore;

const { cartTotalPrice, items } = storeToRefs(cartStore);

const { formatPrice } = usePrice();

const createAPlaceholderImage = (text) => {
    const url = new URL('https://placehold.co/192x192');
    url.searchParams.append('text', text);
    return url.toString();
};

const processedItems = computed(() => {
    return items.value?.map((item) => {
        item.image = item.image_url ?? createAPlaceholderImage(item.name);
        return item;
    });
});
</script>
<template>
    <Head>
        <title>Cart</title>
    </Head>
    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main class="container mx-auto max-w-6xl space-y-12 px-4 py-12">
            <div class="space-y-3">
                <h1 class="text-primary text-4xl font-bold">Cart</h1>
                <div class="divider divider-primary w-24"></div>
                <p class="text-lg">Your trusted partner for comprehensive IT solutions since 2018</p>
            </div>

            <div class="min-h-[calc(100vh-182px)]">
                <div v-if="processedItems?.length">
                    <div class="gap-4 lg:grid lg:grid-cols-12 lg:items-start">
                        <div class="lg:col-span-7">
                            <div class="card bg-base-100">
                                <div class="card-body lg-p-6 p-2 sm:p-4">
                                    <ul>
                                        <li v-for="item in processedItems" :key="item.id" class="border-b-brand-200 flex border-b py-6">
                                            <div class="relative h-24 w-24 overflow-hidden sm:h-48 sm:w-48">
                                                <img
                                                    :alt="item.name"
                                                    loading="lazy"
                                                    class="absolute h-full w-full object-contain object-center text-transparent"
                                                    :src="item.image"
                                                />
                                            </div>

                                            <div class="relative ml-4 flex flex-1 flex-col justify-center gap-y-4 sm:ml-6">
                                                <div class="relative sm:gap-x-6">
                                                    <div class="flex justify-between">
                                                        <p class="pb-2 text-lg font-semibold text-neutral-950">
                                                            {{ item.name }}
                                                        </p>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-x-2">
                                                        <div class="text-primary flex items-center text-base">
                                                            <div class="font-semibold">{{ formatPrice(item.selling_price) }}</div>
                                                        </div>
                                                        <span>x</span>
                                                        <p>{{ item.cart_quantity }} piece</p>
                                                        <p>(VAT Exc)</p>
                                                    </div>
                                                </div>
                                                <div class="inline-flex items-center justify-start gap-2">
                                                    <button
                                                        type="button"
                                                        @click="decrementCartItemQty(item)"
                                                        class="btn btn-sm btn-square btn-accent"
                                                    >
                                                        <font-awesome-icon icon="minus" />
                                                    </button>
                                                    <span>{{ item.cart_quantity }}</span>
                                                    <button
                                                        type="button"
                                                        @click="incrementCartItemQty(item)"
                                                        class="btn btn-sm btn-square btn-accent"
                                                    >
                                                        <font-awesome-icon icon="plus" />
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-5">
                            <div class="card bg-base-100">
                                <div class="card-body lg-p-6 p-2 sm:p-4">
                                    <h2 class="text-lg font-medium">Cart Summary</h2>
                                    <div class="mt-6 space-y-4">
                                        <div class="border-brand-100 flex items-center justify-between border-t pt-6 pb-4">
                                            <div class="text-base font-medium text-neutral-950">Cart Total</div>
                                            <div class="font-semibold">{{ formatPrice(cartTotalPrice) }}</div>
                                        </div>
                                        <div class="space-y-4">
                                            <Link
                                                :href="route('checkout.show')"
                                                class="bg-primary inline-flex w-full items-center justify-center border-transparent px-4 py-2 font-semibold text-white transition hover:opacity-75"
                                            >
                                                Checkout
                                            </Link>

                                            <button
                                                type="button"
                                                @click.prevent="clearCart"
                                                class="text-accent border-accent w-full border bg-transparent px-4 py-2 font-semibold transition hover:opacity-75"
                                            >
                                                Clear Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center gap-y-4 px-4 py-6 text-center lg:py-12">
                    <div class="text-accent border-accent flex h-24 w-24 items-center justify-center rounded-full border">
                        <font-awesome-icon icon="cart-plus" size="2x" />
                    </div>
                    <p class="text-lg font-semibold">Your shopping cart is empty!</p>
                    <p>Browse our products to discover amazing products for your digital collection.</p>
                    <Link class="bg-primary px-3 py-2 text-white hover:shadow-lg" :href="route('products.index')">Shop </Link>
                </div>
            </div>
        </main>
    </base-layout>
</template>
