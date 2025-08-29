<script setup>
import ProductCard from '@/components/ProductCard.vue';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});
</script>
<template>
    <div v-if="category.products.length" class="w-full overflow-x-auto whitespace-nowrap">
        <div class="flex h-full w-full grid-cols-8 gap-2 2xl:grid">
            <ProductCard v-for="product in category.products" :key="product.id" :product="product" />
        </div>
    </div>
</template>
