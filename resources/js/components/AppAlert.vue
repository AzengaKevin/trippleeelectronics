<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    feedback: {
        type: Object,
        required: false,
    },
});

const visible = ref(true);

watch(
    () => props.feedback,
    (newVal) => {
        if (newVal) {
            visible.value = true;
            setTimeout(() => {
                visible.value = false;
            }, 5000);
        }
    },
    { immediate: true },
);

const closeAlert = () => {
    visible.value = false;
};
</script>

<template>
    <transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="feedback && visible"
            :class="[
                'alert flex items-start gap-3 rounded-lg p-4 shadow-sm',
                feedback.type === 'success'
                    ? 'alert-success'
                    : feedback.type === 'error'
                      ? 'alert-error'
                      : feedback.type === 'warning'
                        ? 'alert-warning'
                        : 'alert-info',
            ]"
        >
            <span v-html="feedback.message"></span>
            <button @click="closeAlert" class="ml-auto text-gray-600 hover:text-gray-800">✖</button>
        </div>
    </transition>
</template>
