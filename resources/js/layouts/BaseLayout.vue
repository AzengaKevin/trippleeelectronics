<script setup>
import FooterComponent from '@/components/FooterComponent.vue';
import HeaderComponent from '@/components/HeaderComponent.vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
    treeCategories: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    services: {
        type: Array,
        required: true,
    },
});

const appName = import.meta.env.VITE_APP_NAME;
</script>

<template>
    <header-component :auth="auth" :settings="settings" :tree-categories="treeCategories" :categories="categories" />

    <slot />

    <footer-component :settings="settings" :services="services" />
</template>
