<script setup>
import AppAlert from '@/components/AppAlert.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import BaseLayout from '@/layouts/BaseLayout.vue';
import { useCartStore } from '@/stores/cart';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
    feedback: {
        type: Object,
        default: null,
    },
});

const cartStore = useCartStore();

const { incrementCartItemQty, decrementCartItemQty, clearCart } = cartStore;

const { cartTotalPrice, items, processedCartItems } = storeToRefs(cartStore);

const createAPlaceholderImage = (text) => {
    const url = new URL('https://placehold.co/192x192');
    url.searchParams.append('text', text);
    return url.toString();
};

const processedItems = computed(() => {
    return items.value?.map((item) => {
        item.image = item.image ?? createAPlaceholderImage(item.name);
        return item;
    });
});

const form = useForm({
    customer: {
        name: props.auth.user?.name || '',
        email: props.auth.user?.email || '',
        phone: props.auth.user?.phone || '',
        address: props.auth.user?.address || '',
    },
    amount: cartTotalPrice.value,
});

const submit = () => {
    form.transform((data) => {
        data.items = processedCartItems.value;
        return data;
    }).post(route('checkout.process'), {
        onError: (errors) => {
            console.error(errors);
        },
    });
};
</script>
<template>
    <Head>
        <title>Checkout</title>
    </Head>
    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main class="container mx-auto max-w-6xl space-y-12 px-4 py-12">
            <div class="space-y-3">
                <h1 class="text-primary text-4xl font-bold">Checkout</h1>
                <div class="divider divider-primary w-24"></div>
                <p class="text-lg">Your trusted partner for comprehensive IT solutions since 2018</p>
            </div>

            <app-alert :feedback="feedback" />

            <form v-if="processedItems?.length" @submit.prevent="submit" class="gap-4 lg:grid lg:grid-cols-12 lg:items-start">
                <div class="lg:col-span-7">
                    <div class="card bg-base-100">
                        <div class="card-body space-y-4">
                            <div class="space-y-2">
                                <input-label for="name" value="Name" />
                                <text-input id="name" type="text" class="w-full" v-model="form.customer.name" placeholder="Enter your name" />
                                <input-error :message="form.errors?.['customer.name']" />
                            </div>
                            <div class="space-y-2">
                                <input-label for="email" value="Email Address" />
                                <text-input
                                    id="email"
                                    type="email"
                                    class="w-full"
                                    v-model="form.customer.email"
                                    placeholder="Enter your email address"
                                />
                                <input-error :message="form.errors?.['customer.email']" />
                            </div>
                            <div class="space-y-2">
                                <input-label for="phone" value="Phone Number" />
                                <text-input
                                    id="phone"
                                    type="tel"
                                    class="w-full"
                                    v-model="form.customer.phone"
                                    placeholder="Enter your phone number"
                                />
                                <input-error :message="form.errors?.['customer.phone']" />
                            </div>
                            <div class="space-y-2">
                                <input-label for="address" value="Address" />
                                <text-input
                                    id="address"
                                    type="text"
                                    class="w-full"
                                    v-model="form.customer.address"
                                    placeholder="Enter your address"
                                />
                                <input-error :message="form.errors?.['customer.address']" />
                            </div>
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
                                    <div class="font-semibold">KSH {{ cartTotalPrice }}</div>
                                </div>
                                <div class="space-y-4">
                                    <button type="submit" class="btn btn-primary w-full rounded-none transition hover:opacity-75">Place Order</button>
                                    <Link :href="route('cart')" class="btn btn-outline btn-accent w-full rounded-none transition hover:opacity-75">
                                        Back to Cart
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div v-else class="flex flex-col items-center justify-center gap-y-4 px-4 py-6 text-center lg:py-12">
                <div class="text-accent border-accent flex h-24 w-24 items-center justify-center rounded-full border">
                    <font-awesome-icon icon="cart-plus" size="2x" />
                </div>
                <p class="text-lg font-semibold">Your shopping cart is empty!</p>
                <p>Browse our products to discover amazing products for your digital collection.</p>
                <Link class="bg-primary px-3 py-2 text-white hover:shadow-lg" :href="route('products.index')">Shop </Link>
            </div>
        </main>
    </base-layout>
</template>
