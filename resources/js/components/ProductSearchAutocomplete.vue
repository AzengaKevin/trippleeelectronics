<script setup>
import useProducts from '@/composables/useProducts';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const { products, fetchProducts } = useProducts();

const query = ref('');

const highlightedIndex = ref(-1);

const onArrowDown = () => {
    if (highlightedIndex.value < products.value.length - 1) {
        highlightedIndex.value++;
    }
};

const onArrowUp = () => {
    if (highlightedIndex.value > 0) {
        highlightedIndex.value--;
    }
};

const onEnter = () => {
    if (highlightedIndex.value >= 0) {
        selectSuggestion(products.value[highlightedIndex.value]);
    }
};

const selectSuggestion = (product) => {
    query.value = '';
    router.get(route('products.show', [product.slug]));
};

watch(
    query,
    async (value) => {
        if (value.length >= 2) {
            try {
                await fetchProducts({ query: value, limit: 8 });
            } catch (error) {
                console.log(error);
            }
        } else {
            products.value = [];
        }
    },
    {
        immediate: true,
    },
);
</script>
<template>
    <div class="relative w-full lg:w-96">
        <label class="input w-full">
            <span class="label"><font-awesome-icon icon="search" /></span>
            <input
                v-model="query"
                @keydown.down.prevent="onArrowDown"
                @keydown.up.prevent="onArrowUp"
                @keydown.enter.prevent="onEnter"
                type="search"
                placeholder="Search for products..."
            />
        </label>
        <ul
            v-if="products.length && query.length"
            class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-gray-100 shadow-lg"
        >
            <li
                v-for="(product, index) in products"
                :key="product.id"
                @click="selectSuggestion(product)"
                @mouseover="highlightedIndex = index"
                :class="['cursor-pointer p-3', highlightedIndex === index ? 'bg-primary text-white' : '']"
            >
                <Link href="#">{{ product.name }}</Link>
            </li>
        </ul>
    </div>
</template>
