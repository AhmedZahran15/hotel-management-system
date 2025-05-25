<template>
    <div class="relative h-64 min-h-[300px]">
        <!-- Always render canvas, but overlay loading/error states -->
        <canvas
            ref="chartCanvas"
            class="h-full min-h-[250px] w-full"
            :style="{ visibility: loading || error || !hasData ? 'hidden' : 'visible' }"
        ></canvas>

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

        <!-- No data overlay -->
        <div v-if="!loading && !error && !hasData" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 text-gray-500">
            <p>No data available for {{ selectedYear }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Chart, registerables } from 'chart.js';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

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
const chartData = ref(null);

const { appearance } = useAppearance();

const hasData = computed(() => {
    return chartData.value && chartData.value.labels && chartData.value.labels.length > 0;
});

const generateColors = (count) => {
    const colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'];

    const backgroundColors = [];
    const borderColors = [];

    for (let i = 0; i < count; i++) {
        const color = colors[i % colors.length];
        backgroundColors.push(color);
        borderColors.push(color);
    }

    return { backgroundColors, borderColors };
};

const fetchData = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await fetch(`/dashboard/statistics/top-clients?year=${props.selectedYear}`, {
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
        chartData.value = data;

        if (hasData.value) {
            await nextTick();
            if (chartCanvas.value) {
                createChart(data);
            } else {
                console.error('Chart canvas is null');
            }
        }
    } catch (err) {
        console.error('Error fetching top clients data:', err);
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
    const { backgroundColors, borderColors } = generateColors(data.labels.length);

    // Use appearance composable to detect theme
    const isDarkMode = appearance.value === 'dark' || (appearance.value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    const labelColor = isDarkMode ? '#E5E7EB' : '#111827'; // Light gray for dark mode, very dark gray for light mode

    chartInstance.value = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
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
                        padding: 15,
                        usePointStyle: true,
                        color: labelColor,
                        font: {
                            size: 12,
                        },
                        generateLabels: function (chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const reservationText = value === 1 ? 'reservation' : 'reservations';
                                    return {
                                        text: `${label}: ${value} ${reservationText}`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].borderColor[i],
                                        lineWidth: data.datasets[0].borderWidth,
                                        pointStyle: 'circle',
                                        index: i,
                                    };
                                });
                            }
                            return [];
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            const reservationText = value === 1 ? 'reservation' : 'reservations';
                            return `${label}: ${value} ${reservationText} (${percentage}%)`;
                        },
                    },
                },
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
