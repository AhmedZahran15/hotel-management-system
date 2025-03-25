<script setup lang="ts">
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";

defineProps({
  title: String,
  message: String,
  variant: {
    type: String,
    default: "destructive", // Options: 'default', 'destructive', etc.
  },
  show: Boolean,
});
</script>

<template>
<Alert v-if="show" :variant="variant">
    <slot name="icon"/>
    <div class="flex items-center justify-between px-2">
        <div class="flex flex-col gap-3">
            <AlertTitle>{{ title }}</AlertTitle>
            <AlertDescription>{{ message }}</AlertDescription>
        </div>
        <slot name="dismissBtn"/>
    </div>
</Alert>
</template>
