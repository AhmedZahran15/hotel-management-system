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
        const response = await fetch(`/dashboard/statistics/revenue/${props.selectedYear}`, {
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
        }
    } catch (err) {
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

    chartInstance.value = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Revenue ($)',
                    data: data.data,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#059669',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `Revenue: $${context.parsed.y.toLocaleString()}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        color: '#6B7280',
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(107, 114, 128, 0.1)',
                    },
                    ticks: {
                        color: '#6B7280',
                        callback: function (value) {
                            return '$' + value.toLocaleString();
                        },
                    },
                },
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
        },
    });
};

onMounted(async () => {
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
