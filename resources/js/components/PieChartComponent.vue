<script setup>
import { ArcElement, Chart as ChartJS, Legend, Tooltip } from 'chart.js';
import { Pie } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({
            responsive: true,
            maintainAspectRatio: false,
        }),
    },
});
</script>
<template>
    <Pie :data="data" :options="options" />
</template>
