import { useLocalStorage } from '@vueuse/core';
import { defineStore } from 'pinia';
import { computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    const items = useLocalStorage('shoppit-cart', []);

    const addItemToCart = (product) => {
        let maybeProduct = items.value.find((item) => item.id === product.id);

        if (maybeProduct) {
            maybeProduct.cart_quantity += 1;
        } else {
            product.cart_quantity = 1;

            items.value = [...items.value, product];
        }
    };

    const incrementCartItemQty = (product) => {
        let maybeProduct = items.value.find((item) => item.id === product.id);

        if (maybeProduct) {
            if (maybeProduct.cart_quantity < maybeProduct.stock_quantity) {
                maybeProduct.cart_quantity += 1;
            }
        }
    };

    const decrementCartItemQty = (product) => {
        let maybeProduct = items.value.find((item) => item.id === product.id);

        if (maybeProduct) {
            if (maybeProduct.cart_quantity > 1) {
                maybeProduct.cart_quantity -= 1;
            }
        }
    };

    const removeFromCart = (product) => {
        let filteredProducts = items.value.filter((item) => item.id !== product.id);

        items.value = [...filteredProducts];
    };

    const clearCart = () => {
        items.value = [];
    };

    const cartTotalPrice = computed(() => {
        let sum = items.value.reduce((cumulativeSum, item) => cumulativeSum + item.cart_quantity * item.selling_price, 0);
        return sum.toFixed(2);
    });

    const cartItemsCount = computed(() => {
        let count = items.value.reduce((itemsCount, item) => itemsCount + item.cart_quantity, 0);
        return count;
    });

    const processedCartItems = computed(() => {
        return items.value.map((item) => ({
            product: item.id,
            price: item.selling_price,
            quantity: item.cart_quantity,
        }));
    });

    return {
        items,
        addItemToCart,
        incrementCartItemQty,
        decrementCartItemQty,
        removeFromCart,
        clearCart,
        cartTotalPrice,
        cartItemsCount,
        processedCartItems,
    };
});
