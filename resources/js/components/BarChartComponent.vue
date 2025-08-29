<script setup>
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';

import { Bar } from 'vue-chartjs';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    backgroundColor: {
        type: String,
        default: '#3B82F6',
    },
});

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const data = {
    labels: props.data.map((item) => item.label),
    datasets: [
        {
            label: props.title,
            backgroundColor: props.backgroundColor,
            data: props.data.map((item) => item.value),
        },
    ],
};

const options = {
    responsive: true,
    maintainAspectRatio: false,
};
</script>
<template>
    <Bar :data="data" :options="options" />
</template>
