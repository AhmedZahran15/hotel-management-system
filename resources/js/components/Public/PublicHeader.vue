<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const isAuthenticated = computed(() => auth.value && auth.value.user);

const mobileMenuOpen = ref(false);

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((part) => part.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<template>
    <header class="sticky top-0 z-40 w-full border-b bg-white/90 shadow-sm backdrop-blur dark:border-gray-700 dark:bg-gray-800/95">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo and main nav -->
                <div class="flex items-center">
                    <Link href="/" class="flex-shrink-0">
                        <img class="h-10 w-40" src="/logo.jpg" alt="Hotel Logo" />
                    </Link>

                    <!-- Desktop Navigation -->
                    <nav class="hidden md:ml-6 md:flex md:space-x-8">
                        <Link
                            href="/"
                            class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-800 hover:text-gray-600 dark:text-gray-200 dark:hover:text-white"
                            :class="{ 'border-primary-500 border-b-2': page.url === '/' }"
                        >
                            Home
                        </Link>
                        <Link
                            href="/reservations/make"
                            class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-800 hover:text-gray-600 dark:text-gray-200 dark:hover:text-white"
                            :class="{ 'border-primary-500 border-b-2': page.url.includes('/reservations/make') }"
                        >
                            Make a Reservation
                        </Link>
                    </nav>
                </div>

                <!-- User menu / Auth buttons -->
                <div class="flex items-center">
                    <template v-if="isAuthenticated">
                        <!-- User dropdown -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as="div" class="flex cursor-pointer items-center">
                                <span class="mr-2 hidden text-sm text-gray-800 dark:text-gray-200 md:block">{{ auth.user.name }}</span>
                                <Avatar class="h-8 w-8">
                                    <AvatarImage v-if="auth.user.avatar_image" :src="auth.user.avatar_image" :alt="auth.user.name" />
                                    <AvatarFallback v-else>{{ getInitials(auth.user.name) }}</AvatarFallback>
                                </Avatar>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <Link v-if="auth.user.roles.includes('client')" href="/dashboard/reservations">
                                    <DropdownMenuItem class="cursor-pointer"> My Reservations </DropdownMenuItem>
                                </Link>
                                <Link href="/dashboard">
                                    <DropdownMenuItem class="cursor-pointer"> Dashboard </DropdownMenuItem>
                                </Link>
                                <Link href="/settings/profile">
                                    <DropdownMenuItem class="cursor-pointer"> Profile Settings </DropdownMenuItem>
                                </Link>
                                <Link href="/logout" method="post" as="button" class="w-full">
                                    <DropdownMenuItem class="cursor-pointer"> Log Out </DropdownMenuItem>
                                </Link>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>
                    <template v-else>
                        <!-- Login/Register buttons -->
                        <div class="flex space-x-2">
                            <Link href="/login">
                                <Button variant="outline" size="sm">Log in</Button>
                            </Link>
                            <Link href="/register">
                                <Button size="sm">Register</Button>
                            </Link>
                        </div>
                    </template>

                    <!-- Mobile menu button -->
                    <div class="ml-4 flex items-center md:hidden">
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                        >
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    v-if="mobileMenuOpen"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="md:hidden">
                <div class="space-y-1 pb-3 pt-2">
                    <Link
                        href="/"
                        class="block border-l-4 py-2 pl-3 pr-4 text-base font-medium"
                        :class="
                            page.url === '/'
                                ? 'border-primary-500 text-primary-700 bg-primary-50 dark:bg-gray-700 dark:text-gray-100'
                                : 'border-transparent text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700'
                        "
                    >
                        Home
                    </Link>
                    <Link
                        href="/reservations/make"
                        class="block border-l-4 py-2 pl-3 pr-4 text-base font-medium"
                        :class="
                            page.url.includes('/reservations/make')
                                ? 'border-primary-500 text-primary-700 bg-primary-50 dark:bg-gray-700 dark:text-gray-100'
                                : 'border-transparent text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700'
                        "
                    >
                        Make a Reservation
                    </Link>
                    <template v-if="isAuthenticated">
                        <Link
                            v-if="auth.user.roles.includes('client')"
                            href="/dashboard/reservations"
                            class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            My Reservations
                        </Link>
                        <Link
                            href="/dashboard"
                            class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Dashboard
                        </Link>
                        <Link
                            href="/settings/profile"
                            class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Profile Settings
                        </Link>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="block w-full border-l-4 border-transparent py-2 pl-3 pr-4 text-left text-base font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Log Out
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </header>
</template>
