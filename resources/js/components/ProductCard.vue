<script setup>
import usePrice from '@/composables/usePrice';
import { useCartStore } from '@/stores/cart';
import { useWishListStore } from '@/stores/wishlist';
import { Link } from '@inertiajs/vue3';
import { v4 as uuidv4 } from 'uuid';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const cartStore = useCartStore();
const wishListStore = useWishListStore();

const { addItemToCart } = cartStore;
const { toggleItemWishListPresence } = wishListStore;
const { formatPrice } = usePrice();

const createAPlaceholderImage = (text) => {
    const url = new URL('https://placehold.co/300x300');
    url.searchParams.append('text', text);
    return url.toString();
};

const images = computed(() => {
    const localImages = props.product?.media ?? [];

    if (localImages.length === 0) {
        localImages.push({
            id: uuidv4(),
            preview_url: createAPlaceholderImage(props.product?.name),
            is_primary: true,
        });
    }

    return localImages;
});

const offer = computed(() => props.product.offers?.[0] ?? null);

const discountPercentage = computed(() => {
    if (!offer.value) {
        return true;
    }

    const discount = parseFloat(props.product.selling_price) - parseFloat(offer.value.offer_price);

    const result = (discount / parseFloat(props.product.selling_price)) * 100;

    return parseInt(result);
});

const currentIndex = ref(0);

function nextSlide() {
    currentIndex.value = (currentIndex.value + 1) % images.value?.length;
}

function prevSlide() {
    currentIndex.value = (currentIndex.value - 1 + images.value?.length) % images.value?.length;
}

function startAutoSlide() {
    setInterval(nextSlide, 3000);
}

onMounted(() => {
    startAutoSlide();
});
</script>

<template>
    <div>
        <Link
            class="group border-primary border-brand-blue bg-base-100 relative block h-full w-full min-w-[160px] cursor-pointer space-y-1 border hover:shadow-xl"
            :href="route('products.show', [props.product.slug])"
        >
            <div class="relative aspect-square rounded-xl">
                <div class="relative h-full w-full overflow-hidden">
                    <div class="flex transition-transform duration-500" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
                        <div v-for="(image, index) in images" :key="index" class="w-full flex-none">
                            <div class="flex h-full w-full items-center justify-center">
                                <img
                                    :alt="product.name"
                                    loading="lazy"
                                    width="300"
                                    height="300"
                                    class="aspect-square w-full object-contain text-transparent"
                                    :src="image.preview_url"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute bottom-0 z-10 w-full px-6 transition lg:opacity-0 lg:group-hover:opacity-100">
                    <div class="flex justify-center gap-x-4 text-neutral-500">
                        <button class="action-button">
                            <font-awesome-icon icon="maximize" />
                        </button>

                        <button class="action-button" type="button" @click.prevent="addItemToCart(product)">
                            <font-awesome-icon icon="shopping-cart" />
                        </button>

                        <button class="action-button" type="button" @click.prevent="toggleItemWishListPresence(product)">
                            <font-awesome-icon icon="heart" />
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-3">
                <p class="text-base font-medium text-wrap">
                    {{ product.name }}
                </p>
                <div class="text-primary flex items-center text-base">
                    <div class="font-semibold">{{ formatPrice(offer?.offer_price ?? product.selling_price) }}</div>
                </div>
                <div v-if="offer" class="flex items-center gap-x-2 text-xs">
                    <div class="flex items-center text-red-500 line-through">
                        <div class="font-semibold">{{ formatPrice(product.selling_price) }}</div>
                    </div>
                    <div class="bg-primary absolute top-0 left-4 z-10 flex items-center px-2 py-1 font-bold">
                        <div class="flex items-center text-sm font-semibold text-white">
                            {{ discountPercentage }}
                            <p>% OFF</p>
                        </div>
                    </div>
                </div>
                <div v-if="product.stock_quantity > 10" class="flex items-center gap-x-1 font-medium">
                    <span class="text-green-500">
                        <font-awesome-icon :icon="['regular', 'circle-check']" />
                    </span>
                    In Stock
                </div>
                <div v-else class="flex items-center gap-x-1 font-medium">
                    <span class="text-amber-500">
                        <font-awesome-icon icon="circle-info" />
                    </span>
                    Limited Stock
                </div>
            </div>
        </Link>
    </div>
</template>
