<script setup lang="ts">
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="About Us" />
    <PublicLayout title="About Us">
        <!-- Hero Section -->
        <div class="relative bg-gray-800 text-white">
            <!-- Hero background image -->
            <div class="absolute inset-0 overflow-hidden">
                <img src="/heroSection.jpg" alt="Hotel Exterior" class="h-full w-full object-cover opacity-40" />
                <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
            </div>

            <!-- Hero content -->
            <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32">
                <div class="max-w-3xl">
                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">About Our Hotel</h1>
                    <p class="mt-6 max-w-3xl text-xl text-gray-300">
                        Discover the story behind our luxury hotel and our commitment to exceptional hospitality
                    </p>
                </div>
            </div>
        </div>

        <!-- About Section -->
        <div class="bg-white py-16 dark:bg-background">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-16">
                    <!-- Left column: Story -->
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-foreground">Our Story</h2>
                        <div class="mt-6 space-y-6 text-gray-600 dark:text-muted-foreground">
                            <p>
                                Founded in 2010, our hotel was born from a vision to create an exceptional hospitality experience that combines
                                luxury, comfort, and personalized service. What began as a boutique establishment has grown into a renowned
                                destination for travelers seeking the perfect blend of sophistication and warmth.
                            </p>
                            <p>
                                Over the years, we have continuously evolved while maintaining our core values of excellence, attention to detail, and
                                guest satisfaction. Our dedicated team works tirelessly to ensure that every stay is memorable, whether you're
                                visiting for business or leisure.
                            </p>
                            <p>
                                Today, we are proud to be recognized as one of the premier hotels in the region, with accolades for our service,
                                amenities, and culinary offerings. Our commitment to creating unforgettable experiences remains at the heart of
                                everything we do.
                            </p>
                        </div>
                    </div>

                    <!-- Right column: Image -->
                    <div class="mt-12 lg:mt-0">
                        <div class="overflow-hidden rounded-lg">
                            <img
                                src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80"
                                alt="Hotel Lobby"
                                class="h-full w-full object-cover shadow-xl"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Values Section -->
        <div class="bg-gray-50 py-16 dark:bg-card">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="mb-12 text-3xl font-bold text-gray-900 dark:text-foreground">Our Core Values</h2>
                </div>
                <div class="mt-10 grid grid-cols-1 gap-10 md:grid-cols-3">
                    <!-- Value 1 -->
                    <div class="flex flex-col items-center">
                        <div class="rounded-full bg-primary p-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-white dark:text-background"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-medium text-gray-900 dark:text-foreground">Excellence</h3>
                        <p class="mt-2 text-center text-base text-gray-500 dark:text-muted-foreground">
                            We strive for excellence in every aspect of our service, from immaculate rooms to gourmet dining experiences.
                        </p>
                    </div>

                    <!-- Value 2 -->
                    <div class="flex flex-col items-center">
                        <div class="rounded-full bg-primary p-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-white dark:text-background"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-medium text-gray-900 dark:text-foreground">Personalization</h3>
                        <p class="mt-2 text-center text-base text-gray-500 dark:text-muted-foreground">
                            We believe in tailoring our service to meet the unique needs and preferences of each guest.
                        </p>
                    </div>

                    <!-- Value 3 -->
                    <div class="flex flex-col items-center">
                        <div class="rounded-full bg-primary p-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-white dark:text-background"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-medium text-gray-900 dark:text-foreground">Value</h3>
                        <p class="mt-2 text-center text-base text-gray-500 dark:text-muted-foreground">
                            We are committed to providing exceptional value without compromising on quality or service.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="bg-white py-16 dark:bg-background">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-foreground">Our Leadership Team</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-xl text-gray-500 dark:text-muted-foreground">
                        Meet the dedicated professionals who make our hotel exceptional
                    </p>
                </div>
                <div class="mt-12 grid grid-cols-1 gap-12 md:grid-cols-3">
                    <!-- Team Member 1 -->
                    <div class="text-center">
                        <div class="relative mx-auto h-40 w-40 overflow-hidden rounded-full">
                            <img
                                src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80"
                                alt="CEO"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <h3 class="mt-6 text-lg font-medium text-gray-900 dark:text-foreground">James Wilson</h3>
                        <p class="text-primary dark:text-primary">Chief Executive Officer</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-muted-foreground">
                            With over 25 years in hospitality, James leads our hotel with vision and excellence.
                        </p>
                    </div>

                    <!-- Team Member 2 -->
                    <div class="text-center">
                        <div class="relative mx-auto h-40 w-40 overflow-hidden rounded-full">
                            <img
                                src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=688&q=80"
                                alt="General Manager"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <h3 class="mt-6 text-lg font-medium text-gray-900 dark:text-foreground">Sarah Martinez</h3>
                        <p class="text-primary dark:text-primary">General Manager</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-muted-foreground">
                            Sarah ensures seamless operations and exceptional experiences for all our guests.
                        </p>
                    </div>

                    <!-- Team Member 3 -->
                    <div class="text-center">
                        <div class="relative mx-auto h-40 w-40 overflow-hidden rounded-full">
                            <img
                                src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80"
                                alt="Executive Chef"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <h3 class="mt-6 text-lg font-medium text-gray-900 dark:text-foreground">Michael Thompson</h3>
                        <p class="text-primary dark:text-primary">Executive Chef</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-muted-foreground">
                            Michael creates exquisite culinary experiences that delight our guests' palates.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gray-100 dark:bg-secondary">
            <div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-foreground sm:text-4xl">
                    <span class="block">Experience our hospitality</span>
                </h2>
                <p class="mt-4 text-lg leading-6 text-gray-500 dark:text-muted-foreground">
                    Book your stay today and discover why our guests keep coming back
                </p>
                <div class="mt-8">
                    <Link href="/reservations/make">
                        <Button size="lg" variant="default" class="font-medium"> Make a Reservation </Button>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
