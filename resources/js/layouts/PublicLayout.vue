<script setup lang="ts">
import PublicFooter from '@/components/Public/PublicFooter.vue';
import PublicHeader from '@/components/Public/PublicHeader.vue';
import { Toaster } from '@/components/ui/toast';
import { Head } from '@inertiajs/vue3';

interface Props {
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Hotel Management System',
});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
        <Head :title="props.title" />

        <PublicHeader />

        <main class="mx-auto w-full flex-1">
            <slot />
        </main>

        <PublicFooter />
    </div>
    <Toaster />
</template>
