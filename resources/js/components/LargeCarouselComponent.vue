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
    <div class="relative w-full overflow-hidden lg:max-w-[calc(100%-800px)]">
        <div class="flex transition-transform duration-500" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
            <div v-for="(item, index) in items" :key="index" class="min-w-full">
                <div class="w-full overflow-hidden">
                    <div class="relative h-48 w-[100vw] overflow-hidden sm:h-96 lg:w-auto lg:max-w-[calc(100vw-448px)]">
                        <img
                            :src="item.image_url"
                            class="obje-fill absolute inset-0 h-full w-full bg-cover object-fill text-transparent"
                            :alt="item.title"
                        />

                        <div class="flex h-full w-full flex-col items-center justify-center gap-y-4 text-center backdrop-brightness-50">
                            <div class="max-w-xs text-4xl font-bold text-white sm:max-w-xl sm:text-3xl lg:text-6xl">
                                {{ item.title }}
                            </div>
                            <button
                                type="button"
                                @click.prevent="pushToPath(item.link)"
                                class="border-accent text-accent w-auto rounded-xl border bg-transparent px-8 py-2 text-sm hover:opacity-70 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                See All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button @click="prevSlide" class="absolute top-1/2 left-0 hidden -translate-y-1/2 transform bg-gray-800 p-2 text-white">Prev</button>
        <button @click="nextSlide" class="absolute top-1/2 right-0 hidden -translate-y-1/2 transform bg-gray-800 p-2 text-white">Next</button>
    </div>
</template>
