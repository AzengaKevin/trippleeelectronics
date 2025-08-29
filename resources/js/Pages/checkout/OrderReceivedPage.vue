<script setup>
import BaseLayout from '@/layouts/BaseLayout.vue';
import { useCartStore } from '@/stores/cart';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

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
    order: {
        type: Object,
        required: true,
    },
});

const cartStore = useCartStore();

const { clearCart } = cartStore;

onMounted(() => {
    clearCart();
});
</script>
<template>
    <Head>
        <title>Order Received</title>
    </Head>
    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main class="container mx-auto max-w-6xl space-y-12 px-4 py-12">
            <div class="space-y-3">
                <h1 class="text-primary text-4xl font-bold">Order Received</h1>
                <div class="divider divider-primary w-24"></div>
                <p class="text-lg">Your trusted partner for comprehensive IT solutions since 2018</p>
            </div>

            <div class="flex min-h-[40vh] items-center justify-center">
                <div class="flex flex-col items-center gap-y-4 px-4 py-6 text-center lg:py-12">
                    <div class="flex h-24 w-24 items-center justify-center rounded-full border border-green-600 text-green-600">
                        <font-awesome-icon icon="circle-check" size="2x" />
                    </div>
                    <p class="text-lg font-semibold text-green-700">Order Received!</p>
                    <p>We've received your order and will confirm everything — including your payment — before shipping it.</p>

                    <div class="flex flex-wrap gap-3">
                        <a :href="route('orders.receipt', [order.id])" class="btn btn-primary btn-outline" target="_blank">
                            <font-awesome-icon icon="print" />
                            <span class="text-sm">Receipt</span>
                        </a>
                        <Link :href="route('products.index')" class="btn btn-primary">
                            <font-awesome-icon icon="shopping-cart" />
                            Shop Again !
                        </Link>
                    </div>
                </div>
            </div>
        </main>
    </base-layout>
</template>
