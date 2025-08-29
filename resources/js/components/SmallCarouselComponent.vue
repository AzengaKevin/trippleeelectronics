<script setup>
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});

const currentIndex = ref(0);

function nextSlide() {
    currentIndex.value = (currentIndex.value + 1) % props.items.length;
}

function prevSlide() {
    currentIndex.value = (currentIndex.value - 1 + props.items.length) % props.items.length;
}

function startAutoSlide() {
    setInterval(nextSlide, 5000);
}

const pushToPath = (fullUrl) => {
    router.get(fullUrl);
};

onMounted(() => {
    startAutoSlide();
});
</script>
<template>
    <div class="relative hidden w-[400px] overflow-hidden lg:block">
        <div class="flex transition-transform duration-500" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
            <div v-for="(item, index) in items" :key="index" class="w-full flex-shrink-0">
                <button type="button" @click.prevent="pushToPath(item.link)" class="w-full">
                    <img :src="item.image_url" class="h-96 w-full object-cover" :alt="item.title" />
                </button>
            </div>
        </div>
        <button @click="prevSlide" class="absolute top-1/2 left-0 hidden -translate-y-1/2 transform bg-gray-800 p-2 text-white">Prev</button>
        <button @click="nextSlide" class="absolute top-1/2 right-0 hidden -translate-y-1/2 transform bg-gray-800 p-2 text-white">Next</button>
    </div>
</template>
