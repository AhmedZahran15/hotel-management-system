<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Pagination, PaginationEllipsis, PaginationList, PaginationListItem, PaginationNext, PaginationPrev } from '@/components/ui/pagination';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

// Define types for room and pagination
interface Room {
    number: number;
    name?: string;
    description?: string;
    price: number;
    capacity: number;
    floor_number?: number;
    image_url?: string;
    state: string; // Changed from 'status' to 'state' to match backend
}

interface PaginationLinks {
    prev: string | null;
    next: string | null;
}

interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface RoomsPagination {
    data: Room[];
    links: PaginationLinks;
    meta: PaginationMeta;
}

interface DefaultFilters {
    search: string;
    capacity: string;
    price_min: string;
    price_max: string;
    sort: string;
    page: number;
}
const page = usePage();

const props = defineProps<{
    rooms: RoomsPagination;
}>();

// State
const params = new URLSearchParams(window.location.search);
const filters = ref({
    search: params.get('search') || '',
    capacity: params.get('capacity') || '',
    price_min: params.get('price_min') || '',
    price_max: params.get('price_max') || '',
    sort: params.get('sort') || 'room_price',
    page: parseInt(params.get('page') || '1', 10),
});

// Define default filter values
const defaultFilters: DefaultFilters = {
    search: '',
    capacity: '',
    price_min: '',
    price_max: '',
    sort: 'room_price',
    page: 1,
};

// Methods
const applyFilters = () => {
    // Create a URLSearchParams object for better handling of URL parameters
    const searchParams = new URLSearchParams();

    // Add each filter value individually, only if it's different from default
    Object.entries(filters.value).forEach(([key, value]) => {
        // Use type assertion to ensure TypeScript recognizes key as a valid property of defaultFilters
        if (value && value !== defaultFilters[key as keyof DefaultFilters]) {
            searchParams.append(key, value.toString());
        }
    });

    // Get the route URL without any query parameters
    const baseUrl = route('reservations.make');

    // Use Inertia's get method with properly encoded parameters
    router.get(baseUrl, Object.fromEntries(searchParams), {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = {
        search: '',
        capacity: '',
        price_min: '',
        price_max: '',
        sort: 'room_price', // Reset sort to default
        page: 1, // Add page reset to match DefaultFilters interface
    };
    applyFilters();
};

const goToReservation = (roomId: number) => {
    // Check if user is authenticated
    const auth = page.props.auth;
    if (!auth || !auth.user) {
        // If not logged in, redirect to login page with a return URL
        router.visit(route('login'), {
            data: { redirect: window.location.href },
        });
        return;
    }
    router.get(route('reservations.create', { roomId: roomId }));
};

// Format price as currency - only used as fallback if price_formatted is not available
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

// Helper function to generate page numbers array
const getPageNumbers = (currentPage: number, lastPage: number) => {
    const delta = 1; // Number of pages to show on each side of current page
    let range = [];

    for (let i = Math.max(2, currentPage - delta); i <= Math.min(lastPage - 1, currentPage + delta); i++) {
        range.push(i);
    }

    // Add first page
    if (currentPage - delta > 2) {
        range.unshift('ellipsis-start');
    }
    if (lastPage > 1) {
        range.unshift(1);
    }

    // Add last page
    if (currentPage + delta < lastPage - 1) {
        range.push('ellipsis-end');
    }
    if (lastPage > 1 && !range.includes(lastPage)) {
        range.push(lastPage);
    }

    return range;
};

// For pagination, you can also use URLSearchParams
const goToPage = (pageNumber: number) => {
    const searchParams = new URLSearchParams();
    // Add existing filters
    filters.value.page = pageNumber;
    Object.entries(filters.value).forEach(([key, value]) => {
        if (value && value !== defaultFilters[key as keyof DefaultFilters]) {
            searchParams.append(key, value.toString());
        }
    });

    router.get(route('reservations.make'), Object.fromEntries(searchParams));
};
</script>

<template>
    <Head title="Make a Reservation" />
    <PublicLayout>
        <div class="container px-6 py-8 sm:mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Available Rooms</h1>

                <!-- Add Sort Dropdown -->
                <div class="w-48">
                    <Select v-model="filters.sort" @update:modelValue="applyFilters">
                        <SelectTrigger>
                            <SelectValue placeholder="Sort by" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem class="cursor-pointer" value="room_price">Price (Low to High)</SelectItem>
                            <SelectItem class="cursor-pointer" value="-room_price">Price (High to Low)</SelectItem>
                            <SelectItem class="cursor-pointer" value="capacity">Capacity (Low to High)</SelectItem>
                            <SelectItem class="cursor-pointer" value="-capacity">Capacity (High to Low)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-8 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                <h2 class="mb-4 text-xl font-semibold">Filter Rooms</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Search</label>
                        <Input v-model="filters.search" placeholder="Search by name or description" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Capacity</label>
                        <Select v-model="filters.capacity">
                            <SelectTrigger>
                                <SelectValue placeholder="Select capacity" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem class="cursor-pointer" value="1">1 person</SelectItem>
                                <SelectItem class="cursor-pointer" value="2">2 people</SelectItem>
                                <SelectItem class="cursor-pointer" value="3">3 people</SelectItem>
                                <SelectItem class="cursor-pointer" value="4">4 people</SelectItem>
                                <SelectItem class="cursor-pointer" value="5">5 people</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Min Price</label>
                        <Input v-model="filters.price_min" type="number" min="0" placeholder="Min price" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Max Price</label>
                        <Input v-model="filters.price_max" type="number" min="0" placeholder="Max price" />
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <Button @click="applyFilters">Apply Filters</Button>
                    <Button @click="resetFilters" variant="outline">Reset</Button>
                </div>
            </div>

            <!-- Room Cards -->
            <div v-if="props.rooms.data.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="room in props.rooms.data" :key="room.number" class="flex h-full flex-col overflow-hidden">
                    <!-- Room Image -->
                    <div v-if="room.image_url" class="aspect-video overflow-hidden">
                        <img
                            :src="room.image_url"
                            :alt="room.name || `Room ${room.number}`"
                            class="h-full w-full object-cover transition-transform hover:scale-105"
                        />
                    </div>
                    <div v-else class="flex aspect-video items-center justify-center bg-gray-200 dark:bg-gray-700">
                        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="mx-auto mb-2 h-12 w-12"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                            <p>{{ room.name || `Room ${room.number}` }}</p>
                        </div>
                    </div>

                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="line-clamp-1 text-xl">{{ room.name || `Room ${room.number}` }}</CardTitle>
                            <Badge class="text-nowrap">{{ formatPrice(room.price) }} / night</Badge>
                        </div>
                        <CardDescription class="line-clamp-2" :title="room.description">{{
                            room.description || 'A comfortable room for your stay'
                        }}</CardDescription>
                    </CardHeader>
                    <CardContent class="flex-grow">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mr-2 h-5 w-5 text-gray-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                                <span>Max {{ room.capacity }} {{ room.capacity > 1 ? 'people' : 'person' }}</span>
                            </div>
                            <div v-if="room.floor_number" class="flex items-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mr-2 h-5 w-5 text-gray-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                                <span>Floor {{ room.floor_number }}</span>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button @click="goToReservation(room.number)" class="w-full" :disabled="room.state !== 'available'">
                            Make Reservation
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty state with placeholder room -->
            <div v-else class="grid grid-cols-1 gap-8">
                <div class="rounded-lg bg-white p-8 text-center shadow-sm dark:bg-gray-800">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="mx-auto h-16 w-16 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium">No rooms match your filters</h3>
                    <p class="mt-2 text-gray-500">Try adjusting your search criteria to find available rooms.</p>
                    <Button @click="resetFilters" variant="outline" class="mt-4">Reset Filters</Button>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="props.rooms.data.length > 0" class="mt-8 flex justify-center">
                <Pagination class="mx-auto flex w-auto justify-center">
                    <PaginationList class="flex items-center gap-1">
                        <PaginationListItem>
                            <PaginationPrev
                                :disabled="props.rooms.meta.current_page <= 1"
                                @click="props.rooms.links.prev ? router.get(props.rooms.links.prev) : null"
                            />
                        </PaginationListItem>

                        <template v-for="(page, index) in getPageNumbers(props.rooms.meta.current_page, props.rooms.meta.last_page)" :key="index">
                            <PaginationListItem v-if="page === 'ellipsis-start' || page === 'ellipsis-end'">
                                <PaginationEllipsis />
                            </PaginationListItem>
                            <PaginationListItem v-else>
                                <Button
                                    variant="outline"
                                    class="h-10 w-10 p-0"
                                    :class="[
                                        page === props.rooms.meta.current_page
                                            ? 'bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground'
                                            : 'hover:bg-muted hover:text-foreground',
                                    ]"
                                    @click="goToPage(page)"
                                >
                                    {{ page }}
                                </Button>
                            </PaginationListItem>
                        </template>

                        <PaginationListItem>
                            <PaginationNext
                                :disabled="props.rooms.meta.current_page >= props.rooms.meta.last_page"
                                @click="props.rooms.links.next ? router.get(props.rooms.links.next) : null"
                            />
                        </PaginationListItem>
                    </PaginationList>
                </Pagination>
            </div>
        </div>
    </PublicLayout>
</template>
