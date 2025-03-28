<script setup lang="ts">
import { watch, ref, onMounted, onUnmounted,computed } from "vue";
import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction
} from "@/components/ui/alert-dialog";
import Alert from '@/components/Shared/Alert.vue';
import { AlertCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const props = defineProps({
    title: { type: String, required: true },          // Modal Title
    description: { type: String, default: "" },    // Modal Description
    confirmText: { type: String, default: "Confirm" }, // Confirm Button Text
    cancelText: { type: String, default: "Cancel" },   // Cancel Button Text
    confirmVariant: { type: String, default: "destructive" }, // Button variant (e.g., 'default', 'destructive')
    open: Boolean, // Controls modal externally
    contentClass: {type: String, default:"max-w-3xl  max-h-[90vh] overflow-y-auto"}, // Class for content container
    disableEsc: { type: Boolean, default: true },
    buttonsVisible:{type:Boolean ,default:true},
    errors: {type:Object, default: {}}
});
console.log(props.errors);
const errors = computed(() => props.errors);
const emit = defineEmits(["confirm", "update:open"]);

// **Ensure internal state follows external control**
const showDialog = ref(props.open);

watch(() => props.open, (newValue) => {showDialog.value = newValue;});

// Prevent closing with Esc key
const preventEscClose = (event:KeyboardEvent) => {
    if (props.disableEsc && event.key === "Escape") {
        event.preventDefault();
        event.stopPropagation();
    }
    else if(event.key === "Escape"){
        emit("update:open", false);}
};
// Add & remove event listeners
onMounted(() => {
    document.addEventListener("keydown", preventEscClose);
});
onUnmounted(() => {
    document.removeEventListener("keydown", preventEscClose);
});
// Open/close the modal

const closeModal = () => {
    showDialog.value = false;

    emit("update:open", false);
};
const confirmAction = () => {
    emit("confirm"); // Emits event for parent to handle action
    closeModal();
};
const dismissError = (error) => {
    delete errors.value[error];
};
</script>

<template>
    <AlertDialog v-model:open="showDialog">
        <AlertDialogTrigger as-child>
            <slot name="trigger">
            </slot>
        </AlertDialogTrigger>
        <AlertDialogContent :class="contentClass">
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    <Alert class="my-1"
                        v-for="(value, index) of errors"
                        :key="index"
                        :show="true"
                        :title="index"
                        :message="value">
                        <template v-slot:icon><AlertCircle class="h-4 w-4" /></template>
                        <template v-slot:dismissBtn>
                            <Button class="bg-white text-black" @click="dismissError(index)">Dismiss</Button>
                        </template>
                    </Alert>
                    <slot name="description">{{ description }}</slot>
                </AlertDialogDescription>
            </AlertDialogHeader>

            <AlertDialogFooter>
                <slot name="footer"/>
                <div v-if="buttonsVisible" class="flex justify-end gap-3">
                    <AlertDialogCancel @click="closeModal">{{ cancelText }}</AlertDialogCancel>
                    <AlertDialogAction :variant="confirmVariant" @click="confirmAction">
                        <slot name="confirm-text">{{ confirmText }}</slot>
                    </AlertDialogAction>
                </div>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
