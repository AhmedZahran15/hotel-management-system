<script setup lang="ts">
import type { FieldProps } from './interface'
import { Button } from '@/components/ui/button'
import { FormControl, FormDescription, FormField, FormItem, FormMessage } from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import { TrashIcon } from 'lucide-vue-next'
import { ref } from 'vue'
import AutoFormLabel from './AutoFormLabel.vue'
import { beautifyObjectName } from './utils'

defineProps<FieldProps>()

const inputFile = ref<File>()

</script>

<template>
<FormField v-slot="slotProps" :name="fieldName">
    <FormItem v-bind="$attrs">
        <AutoFormLabel v-if="!config?.hideLabel" :required="required">
            {{ config?.label || beautifyObjectName(label ?? fieldName) }}
        </AutoFormLabel>
        <FormControl>
            <slot v-bind="slotProps">
            <Input
            v-if="!inputFile"
            type="file"
            v-bind="{ ...config?.inputProps }"
            :disabled="config?.inputProps?.disabled ?? disabled"
            @change="(ev: InputEvent) => {
                const file = (ev.target as HTMLInputElement).files?.[0]
                inputFile = file
                slotProps.componentField.onInput(file) // Store the file directly instead of base64
            }"
            />
            <div v-else class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-transparent pl-3 pr-1 py-1 text-sm shadow-sm transition-colors">
            <p>{{ inputFile?.name }}</p>
            <Button
                :size="'icon'"
                :variant="'ghost'"
                class="h-[26px] w-[26px]"
                aria-label="Remove file"
                type="button"
                @click="() => {
                inputFile = undefined
                slotProps.componentField.onInput(undefined)
            }"
            >
            <TrashIcon :size="16" />
            </Button>
            </div>
            </slot>
        </FormControl>
        <FormDescription v-if="config?.description">
            {{ config.description }}
        </FormDescription>
        <FormMessage />
    </FormItem>
</FormField>
</template>
