<script setup lang="ts">
import { watch, ref, onMounted, onUnmounted } from "vue";
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

const props = defineProps({
    title: { type: String, required: true },          // Modal Title
    description: { type: String, default: "" },    // Modal Description
    confirmText: { type: String, default: "Confirm" }, // Confirm Button Text
    cancelText: { type: String, default: "Cancel" },   // Cancel Button Text
    confirmVariant: { type: String, default: "destructive" }, // Button variant (e.g., 'default', 'destructive')
    open: Boolean, // Controls modal externally
    contentClass: {type: String, default:"max-w-2xl"},
    disableEsc: { type: Boolean, default: true },
    buttonsVisible:{type:Boolean ,default:true}

});

const emit = defineEmits(["confirm", "update:open"]);

// **Ensure internal state follows external control**
const showDialog = ref(props.open);

watch(() => props.open, (newValue) => {
    showDialog.value = newValue;
});

// Prevent closing with Esc key
const preventEscClose = (event:KeyboardEvent) => {
    if (props.disableEsc && event.key === "Escape") {
        event.preventDefault();
        event.stopPropagation();
    }
};
// Add & remove event listeners
onMounted(() => {
    document.addEventListener("keydown", preventEscClose);
});
onUnmounted(() => {
    document.removeEventListener("keydown", preventEscClose);
});
// Open/close the modal
const openModal = () => {
    showDialog.value = true;
    emit("update:open", true);
};
const closeModal = () => {
    showDialog.value = false;
    emit("update:open", false);
};
const confirmAction = () => {
    emit("confirm"); // Emits event for parent to handle action
    closeModal();
};
</script>

<template>
    <AlertDialog v-model:open="showDialog">
        <AlertDialogTrigger as-child>
            <slot name="trigger">
                <button @click="openModal" class="btn btn-primary">Open Modal</button>
            </slot>
        </AlertDialogTrigger>

        <AlertDialogContent :class="contentClass">
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    <slot name="description">{{ description }}</slot>
                </AlertDialogDescription>
            </AlertDialogHeader>

            <AlertDialogFooter>
                <slot name="footer"/>
                <div v-if="buttonsVisible">
                    <AlertDialogCancel @click="closeModal">{{ cancelText }}</AlertDialogCancel>
                    <AlertDialogAction :variant="confirmVariant" @click="confirmAction">
                        <slot name="confirm-text">{{ confirmText }}</slot>
                    </AlertDialogAction>
                </div>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
