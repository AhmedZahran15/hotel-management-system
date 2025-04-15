<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';
import { Laptop, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

// Define component props
interface Props {
    variant?: 'default' | 'outline' | 'ghost';
    size?: 'default' | 'sm' | 'lg' | 'icon';
    align?: 'start' | 'center' | 'end';
    displayMode?: 'icon' | 'text' | 'full';
}

// Props with default values
withDefaults(defineProps<Props>(), {
    variant: 'outline',
    size: 'icon',
    align: 'end',
    displayMode: 'icon',
});
// Use the appearance composable
const { appearance, updateAppearance } = useAppearance();

// Compute the icon based on current theme
const currentIcon = computed(() => {
    switch (appearance.value) {
        case 'light':
            return Sun;
        case 'dark':
            return Moon;
        default:
            return Laptop;
    }
});

// Text label for current theme
const currentLabel = computed(() => {
    switch (appearance.value) {
        case 'light':
            return 'Light';
        case 'dark':
            return 'Dark';
        default:
            return 'System';
    }
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button :variant="variant" :size="size" class="gap-1">
                <component :is="currentIcon" class="h-[1.2rem] w-[1.2rem]" />
                <span v-if="displayMode === 'full'">{{ currentLabel }}</span>
                <span v-else-if="displayMode === 'text'" class="sr-only">Toggle theme</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent :align="align">
            <DropdownMenuItem @click="updateAppearance('light')">
                <Sun class="mr-2 h-4 w-4" />
                <span>Light</span>
            </DropdownMenuItem>
            <DropdownMenuItem @click="updateAppearance('dark')">
                <Moon class="mr-2 h-4 w-4" />
                <span>Dark</span>
            </DropdownMenuItem>
            <DropdownMenuItem @click="updateAppearance('system')">
                <Laptop class="mr-2 h-4 w-4" />
                <span>System</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
