<script setup>
import LargeCarouselComponent from '@/components/LargeCarouselComponent.vue';
import SmallCarouselComponent from '@/components/SmallCarouselComponent.vue';
import { computed } from 'vue';

const props = defineProps({
    carousels: {
        type: Array,
        default: () => [],
    },
});

const leftSideCarousels = computed(() => props.carousels.filter((carousel) => carousel.position === 'left'));

const rightSideCarousels = computed(() => props.carousels.filter((carousel) => carousel.position === 'right'));

const centerCarousels = computed(() => props.carousels.filter((carousel) => carousel.position === 'center'));
</script>
<template>
    <div class="px-4 py-2">
        <div v-if="carousels.length" class="gap-x-2 lg:flex">
            <small-carousel-component :items="leftSideCarousels" />

            <large-carousel-component :items="centerCarousels" />

            <small-carousel-component :items="rightSideCarousels" />
        </div>
    </div>
</template>
