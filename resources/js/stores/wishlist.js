import { useLocalStorage } from '@vueuse/core';
import { defineStore } from 'pinia';
import { computed } from 'vue';

export const useWishListStore = defineStore('wishlist', () => {
    const items = useLocalStorage('wishlist', []);

    const toggleItemWishListPresence = (product) => {
        let maybeProduct = items.value.find((item) => item.id === product.id);

        if (maybeProduct) {
            let filteredProducts = items.value.filter((item) => item.id !== product.id);

            items.value = [...filteredProducts];
        } else {
            items.value = [...items.value, product];
        }
    };

    const clearWishList = () => {
        items.value = [];
    };

    const wishListItemsCount = computed(() => items.value.length);

    return {
        items,
        wishListItemsCount,
        toggleItemWishListPresence,
        clearWishList,
    };
});
