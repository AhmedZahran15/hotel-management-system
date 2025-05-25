<template>
    <div class="relative h-64 min-h-[300px]">
        <!-- Always render canvas, but overlay loading/error states -->
        <canvas ref="chartCanvas" class="h-full min-h-[250px] w-full" :style="{ visibility: loading || error ? 'hidden' : 'visible' }"></canvas>

        <!-- Loading overlay -->
        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75">
            <div class="h-8 w-8 animate-spin rounded-full border-b-2 border-indigo-600"></div>
            <span class="ml-2">Loading chart...</span>
        </div>

        <!-- Error overlay -->
        <div v-if="error" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 text-red-500">
            <div class="text-center">
                <p>{{ error }}</p>
                <button @click="fetchData" class="mt-2 rounded bg-red-500 px-3 py-1 text-sm text-white">Retry</button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Chart, registerables } from 'chart.js';
import { nextTick, onMounted, ref, watch } from 'vue';

Chart.register(...registerables);

const props = defineProps({
    selectedYear: {
        type: Number,
        required: true,
    },
});

const chartCanvas = ref(null);
const chartInstance = ref(null);
const loading = ref(false);
const error = ref(null);

const fetchData = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await fetch(`/dashboard/statistics/male-female?year=${props.selectedYear}`, {
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        const data = await response.json();

        await nextTick();
        if (chartCanvas.value) {
            createChart(data);
        } else {
            console.error('Chart canvas is null');
        }
    } catch (err) {
        console.error('Error fetching male-female data:', err);
        error.value = err.message;
    } finally {
        loading.value = false;
    }
};

const createChart = (data) => {
    if (chartInstance.value) {
        chartInstance.value.destroy();
    }

    if (!chartCanvas.value) {
        console.error('Canvas element not found');
        return;
    }

    const ctx = chartCanvas.value.getContext('2d');

    const total = data.male + data.female;
    const malePercentage = total > 0 ? ((data.male / total) * 100).toFixed(1) : 0;
    const femalePercentage = total > 0 ? ((data.female / total) * 100).toFixed(1) : 0;

    chartInstance.value = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [`Male (${malePercentage}%)`, `Female (${femalePercentage}%)`],
            datasets: [
                {
                    data: [data.male, data.female],
                    backgroundColor: [
                        '#3B82F6', // Blue for male
                        '#EC4899', // Pink for female
                    ],
                    borderColor: ['#2563EB', '#DB2777'],
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            return `${label}: ${value} reservations`;
                        },
                    },
                },
            },
        },
    });
};

onMounted(async () => {
    // Wait for DOM to be fully ready
    await nextTick();
    setTimeout(() => {
        if (chartCanvas.value) {
            fetchData();
        } else {
            console.error('Canvas still not available after mount');
        }
    }, 200);
});

watch(
    () => props.selectedYear,
    () => {
        fetchData();
    },
);
</script>
