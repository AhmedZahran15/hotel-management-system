<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

// Import Chart Components
import CountriesChart from '@/components/Charts/CountriesChart.vue';
import MaleFemaleChart from '@/components/Charts/MaleFemaleChart.vue';
import RevenueChart from '@/components/Charts/RevenueChart.vue';
import TopClientsChart from '@/components/Charts/TopClientsChart.vue';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Statistics', href: '/dashboard/statistics' },
];

// Global year filter
const selectedYear = ref<number>(new Date().getFullYear());
const availableYears = ref<number[]>([]);

// Generate years from 2020 to current year + 1
onMounted(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let year = 2020; year <= currentYear + 1; year++) {
        years.push(year);
    }
    availableYears.value = years.reverse(); // Most recent first
});

const handleYearChange = (year: string) => {
    selectedYear.value = parseInt(year);
};
</script>

<template>
    <Head title="Statistics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header with Global Year Filter -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Statistics Dashboard</h1>
                    <p class="text-muted-foreground">Comprehensive analytics for hotel reservations and client data</p>
                </div>

                <div class="flex items-center gap-2">
                    <Label for="year-filter" class="text-sm font-medium">Filter by Year:</Label>
                    <Select :default-value="selectedYear.toString()" @update:model-value="handleYearChange">
                        <SelectTrigger id="year-filter" class="w-32">
                            <SelectValue :placeholder="selectedYear.toString()" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="year in availableYears" :key="year" :value="year.toString()">
                                {{ year }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Male vs Female Reservations -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                            Male vs Female Reservations
                        </CardTitle>
                        <CardDescription> Gender distribution of hotel reservations for {{ selectedYear }} </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <MaleFemaleChart :selected-year="selectedYear" />
                    </CardContent>
                </Card>

                <!-- Revenue Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            Monthly Revenue
                        </CardTitle>
                        <CardDescription> Revenue generated from reservations throughout {{ selectedYear }} </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <RevenueChart :selected-year="selectedYear" />
                    </CardContent>
                </Card>

                <!-- Countries Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-purple-500"></span>
                            Reservations by Country
                        </CardTitle>
                        <CardDescription> Distribution of reservations by client's country for {{ selectedYear }} </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <CountriesChart :selected-year="selectedYear" />
                    </CardContent>
                </Card>

                <!-- Top Clients Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                            Top 10 Clients
                        </CardTitle>
                        <CardDescription> Clients with the most reservations in {{ selectedYear }} </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <TopClientsChart :selected-year="selectedYear" />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
