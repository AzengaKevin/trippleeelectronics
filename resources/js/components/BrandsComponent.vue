<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    brands: {
        type: Array,
        required: true,
    },
});

const createAPlaceholderImage = (text) => {
    const url = new URL('https://placehold.co/144x72');

    url.searchParams.append('text', text);

    return url.toString();
};

const spaceBetween = ref('8');

const autoplay = ref({
    delay: 5000,
});

const breakpoints = ref({
    640: {
        slidesPerView: 4,
    },
    768: {
        slidesPerView: 6,
    },
    1024: {
        slidesPerView: 8,
    },
});

const processedBrands = computed(() => {
    return props.brands?.map(({ id, name, slug, image_url }) => ({
        id,
        name,
        slug,
        image_url: image_url ? image_url : createAPlaceholderImage(name),
    }));
});
</script>

<template>
    <section v-if="processedBrands?.length" class="px-4">
        <div class="group relative">
            <div class="scrollbar-none flex snap-x snap-mandatory space-x-4 overflow-x-auto scroll-smooth py-4 transition-all duration-500">
                <div v-for="brand in processedBrands" :key="brand.id" class="w-48 flex-shrink-0 snap-center">
                    <Link
                        :href="route('products.categories.index', [brand.slug])"
                        class="bg-base-100 rounded-box block p-4 shadow transition-all duration-300 hover:shadow-md"
                    >
                        <img :src="brand.image_url" :alt="brand.name" class="mx-auto h-32 w-full object-contain" />
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
