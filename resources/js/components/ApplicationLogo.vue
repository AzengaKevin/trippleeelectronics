<script setup>
defineProps({
    color: {
        type: String,
        default: 'text-primary',
    },
    size: {
        type: String,
        default: '3x',
    },
});

const appName = import.meta.env.VITE_APP_NAME;
</script>
<template>
    <img src="@/assets/images/logo.png" :alt="`${appName} Logo`" class="h-32" />
</template>
