<script setup lang="ts">
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <PublicLayout title="Welcome to Our Hotel">
        <!-- Hero Section -->
        <div class="relative bg-gray-800 text-white">
            <!-- Hero background image -->
            <div class="absolute inset-0 overflow-hidden">
                <img src="heroSection.jpg" alt="Luxury Hotel" class="h-full w-full object-cover opacity-50" />
                <div class="absolute inset-0 bg-gray-900 opacity-50 blur-sm"></div>
            </div>

            <!-- Hero content -->
            <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32">
                <div class="max-w-3xl">
                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">Experience Luxury & Comfort</h1>
                    <p class="mt-6 max-w-3xl text-xl text-gray-300">
                        Book your stay at our premium hotel and enjoy world-class amenities, exceptional service, and unforgettable experiences.
                    </p>
                    <div class="mt-10 flex gap-4">
                        <Link href="/reservations/make">
                            <Button size="lg" class="font-medium">Book Now</Button>
                        </Link>
                        <Button variant="outline" size="lg" class="border-white text-black hover:bg-white/70"> Learn More </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features section -->
        <div class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-primary-600 text-base font-semibold uppercase tracking-wide">Features</h2>
                    <p class="mt-1 text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight">Why Choose Our Hotel</p>
                    <p class="mx-auto mt-5 max-w-xl text-xl text-gray-500">
                        Experience the perfect blend of luxury, comfort, and exceptional service at our premium hotel.
                    </p>
                </div>

                <div class="mt-12">
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="pt-6">
                            <div class="flow-root rounded-lg bg-gray-50 px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center rounded-md bg-blue-500 p-3 shadow-lg">
                                            <svg
                                                class="h-6 w-6 text-white"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium text-gray-900">Luxury Accommodations</h3>
                                    <p class="mt-5 text-base text-gray-500">
                                        Our elegantly appointed rooms and suites offer the perfect blend of comfort, style, and modern amenities.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <div class="flow-root rounded-lg bg-gray-50 px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center rounded-md bg-blue-500 p-3 shadow-lg">
                                            <svg
                                                class="h-6 w-6 text-white"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium text-gray-900">24/7 Service</h3>
                                    <p class="mt-5 text-base text-gray-500">
                                        Our dedicated staff is available around the clock to ensure your stay is comfortable and hassle-free.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <div class="flow-root rounded-lg bg-gray-50 px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center rounded-md bg-blue-500 p-3 shadow-lg">
                                            <svg
                                                class="h-6 w-6 text-white"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium text-gray-900">Fine Dining</h3>
                                    <p class="mt-5 text-base text-gray-500">
                                        Enjoy exceptional cuisine at our on-site restaurants, offering a diverse range of culinary experiences.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gray-100">
            <div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
                <h2 class="text-3xl font-extrabold text-neutral-800 sm:text-4xl">
                    <span class="block">Ready for an unforgettable stay?</span>
                </h2>
                <p class="text-primary-200 mt-4 text-lg leading-6">Book your room now and experience our world-class hospitality.</p>
                <Link href="/reservations/make">
                    <Button
                        size="lg"
                        variant="default"
                        class="mt-8 inline-flex w-full items-center justify-center border-2 border-white text-white hover:bg-white hover:text-neutral-900 sm:w-auto"
                    >
                        Make a Reservation
                    </Button>
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
