<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    categories: Array,
});

const createAPlaceholderImage = (text) => {
    const url = new URL('https://placehold.co/144x72');

    url.searchParams.append('text', text);

    return url.toString();
};

const processedCategories = computed(() => {
    return props.categories?.map(({ id, name, image_url, slug }) => {
        return {
            id,
            name,
            image_url: image_url !== '' ? image_url : createAPlaceholderImage(name),
            slug,
        };
    });
});
</script>

<template>
    <div v-if="processedCategories?.length" class="scrollbar-thin scrollbar-none overflow-x-auto whitespace-nowrap">
        <div class="animate-marquee-disable flex items-center gap-x-1">
            <Link
                v-for="(category, index) in processedCategories"
                :key="index"
                :href="route('products.categories.index', { slug: category.slug })"
                class="group relative flex h-16 w-20 flex-none flex-col items-center sm:h-20 sm:w-28 lg:h-20"
            >
                <img :src="category.image_url" :alt="category.name" loading="lazy" class="absolute inset-0 h-full w-full object-cover" />
                <div
                    class="from-primary to-accent absolute inset-x-0 bottom-0 truncate bg-black px-2 text-center text-xs font-medium text-white group-hover:bg-gradient-to-r sm:text-sm"
                >
                    {{ category.name }}
                </div>
            </Link>
        </div>
    </div>
</template>
