<script setup>
import ProductCard from '@/components/ProductCard.vue';
import usePrice from '@/composables/usePrice';
import BaseLayout from '@/layouts/BaseLayout.vue';
import { useCartStore } from '@/stores/cart';
import { useWishListStore } from '@/stores/wishlist';
import { Head, router } from '@inertiajs/vue3';
import { v4 as uuidv4 } from 'uuid';
import { computed, ref } from 'vue';

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
    product: {
        type: Object,
        required: true,
    },
    recommendedProducts: {
        type: Array,
        required: true,
    },
});

const cartStore = useCartStore();

const wishListStore = useWishListStore();

const { formatPrice } = usePrice();

const createAPlaceholderImage = (text) => {
    const url = new URL('https://placehold.co/600x400');

    url.searchParams.append('text', text);

    return url.toString();
};

const whatsappUrl = computed(() => `https://api.whatsapp.com/send?phone=+254705079270&text=Is%20"${props.product.name}"%20available`);

const images = computed(() => {
    const localImages = props.product.media ?? [];

    if (!localImages.length) {
        localImages.push({
            id: uuidv4(),
            original_url: createAPlaceholderImage(props.product?.name),
            preview_url: createAPlaceholderImage(props.product?.name),
        });
    }

    return localImages;
});

const currentIndex = ref(0);

const nextImage = () => {
    if (currentIndex.value < images.value?.length - 1) {
        currentIndex.value += 1;
    } else {
        currentIndex.value = 0;
    }
};

const prevImage = () => {
    if (currentIndex.value > 0) {
        currentIndex.value -= 1;
    } else {
        currentIndex.value = images.value?.length - 1;
    }
};

const setImage = (index) => {
    currentIndex.value = index;
};

const currentImage = computed(() => images.value?.[currentIndex.value]?.original_url);

const { addItemToCart } = cartStore;

const { toggleItemWishListPresence } = wishListStore;

const handleBuyNow = (product) => {
    addItemToCart(product);

    router.visit(route('checkout.show'));
};
</script>
<template>
    <Head>
        <title>{{ product.name }}</title>
    </Head>
    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main v-if="product" class="p-4">
            <div class="bg-base-100 flex flex-col gap-2 lg:flex-row">
                <div class="bg-base-100 flex w-full flex-col gap-2 rounded-sm p-2 shadow-sm sm:flex-row-reverse sm:items-center lg:w-1/2">
                    <div class="relative h-96 w-full items-center justify-center">
                        <img :src="currentImage" :alt="product.name" class="absolute inset-0 h-96 w-full rounded-sm object-contain" />

                        <!-- Previous Button -->
                        <button @click="prevImage" class="btn btn-circle absolute top-1/2 left-2 -translate-y-1/2 transform rounded-full p-2">
                            <font-awesome-icon icon="chevron-left" />
                        </button>

                        <!-- Next Button -->
                        <button @click="nextImage" class="btn btn-circle absolute top-1/2 right-2 -translate-y-1/2 transform rounded-full p-2">
                            <font-awesome-icon icon="chevron-right" />
                        </button>
                    </div>
                    <div class="scrollbar-none overflow-x-auto whitespace-nowrap sm:h-96">
                        <div class="flex flex-row gap-2 sm:flex-col">
                            <img
                                v-for="(image, index) in images"
                                :key="index"
                                :src="image.preview_url"
                                :alt="product.name"
                                class="sm{size-16 size-12 cursor-pointer rounded-sm object-contain lg:size-20"
                                :class="{ 'border-primary border-2': currentIndex === index }"
                                @click="setImage(index)"
                            />
                        </div>
                    </div>
                </div>
                <div class="bg-base-100 w-full rounded-sm p-6 shadow-sm lg:w-1/2">
                    <h2 class="mb-4 text-3xl font-bold">{{ product.name }}</h2>
                    <p class="text-primary mb-4 text-xl">{{ formatPrice(product.selling_price) }}</p>

                    <hr class="text-primary my-2" />

                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                        <div class="flex min-w-fit items-center gap-x-2">
                            <h3 class="font-semibold">Stock Status:</h3>
                            <div v-if="product.stock_quantity > 10" class="flex items-center gap-x-1">
                                <span class="text-green-500">
                                    <font-awesome-icon :icon="['regular', 'circle-check']" />
                                </span>
                                In Stock
                            </div>
                            <div v-else class="flex items-center gap-x-1">
                                <span class="text-amber-500">
                                    <font-awesome-icon icon="circle-info" />
                                </span>
                                Limited Stock
                            </div>
                        </div>
                        <div class="flex min-w-fit items-center gap-x-1">
                            <h3 class="font-semibold">SKU:</h3>
                            <div>{{ product.sku }}</div>
                        </div>
                    </div>

                    <p class="mb-6">{{ product.description }}</p>

                    <button class="btn btn-link" onclick="summary.showModal()">Read More</button>

                    <dialog id="summary" class="modal">
                        <div class="modal-box w-11/12 max-w-5xl">
                            <form method="dialog">
                                <button class="btn btn-sm btn-circle btn-ghost absolute top-2 right-2">✕</button>
                            </form>
                            <h3 class="text-lg font-bold">Product Summary</h3>
                            <hr class="my-6" />
                            <div v-html="product.more_description" />
                        </div>
                    </dialog>

                    <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-6 text-sm">
                        <button
                            type="button"
                            @click="addItemToCart(product)"
                            class="bg-primary flex w-auto items-center gap-x-2 border-transparent px-4 py-2 font-semibold text-white transition hover:opacity-75"
                        >
                            <span>Add to Cart</span>
                            <font-awesome-icon icon="shopping-cart" />
                        </button>

                        <button
                            type="button"
                            @click="handleBuyNow(product)"
                            class="bg-accent flex w-auto items-center gap-x-2 border-transparent px-4 py-2 font-semibold text-white transition hover:opacity-75"
                        >
                            <span>Buy Now</span>
                            <font-awesome-icon icon="credit-card" />
                        </button>
                        <button
                            type="button"
                            @click="toggleItemWishListPresence(product)"
                            class="text-primary border-primary flex w-auto items-center gap-x-2 border bg-transparent px-4 py-2 text-sm font-semibold transition hover:opacity-75"
                        >
                            <span>Add to Wishlist</span>
                            <font-awesome-icon icon="heart" />
                        </button>
                        <a
                            target="_blank"
                            :href="whatsappUrl"
                            class="flex w-auto items-center gap-x-2 border border-[#25D366] bg-transparent px-4 py-2 text-sm font-semibold text-[#25D366] transition hover:opacity-75"
                        >
                            <span>Enquiries</span>
                            <font-awesome-icon :icon="['fab', 'whatsapp']" />
                        </a>
                    </div>
                </div>
            </div>
            <section class="py-4">
                <div class="">
                    <h2 class="text-center text-xl font-semibold sm:text-left">Related Products</h2>
                    <div class="mt-4 grid w-full grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 2xl:grid-cols-8">
                        <ProductCard v-for="product in recommendedProducts" :key="product.id" :product="product" />
                    </div>
                </div>
            </section>
        </main>
    </base-layout>
</template>
