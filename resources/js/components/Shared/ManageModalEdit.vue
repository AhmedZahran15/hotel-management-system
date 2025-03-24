<script setup lang="ts">
import { watch, ref } from "vue";
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from "@/components/ui/alert-dialog";

const props = defineProps({
  title: { type: String, required: true },
  description: { type: String, default: "" },
  confirmText: { type: String, default: "Confirm" },
  cancelText: { type: String, default: "Cancel" },
  confirmVariant: { type: String, default: "destructive" },
  open: Boolean,
  contentClass: { type: String, default: "max-w-2xl" },
  hideFooter: { type: Boolean, default: false }, // ✅ New prop to control footer visibility
});

const emit = defineEmits(["confirm", "update:open"]);

const showDialog = ref(props.open);

watch(
  () => props.open,
  (newValue) => {
    showDialog.value = newValue;
  }
);

const closeModal = () => {
  showDialog.value = false;
  emit("update:open", false);
};

const confirmAction = () => {
  emit("confirm");
  closeModal();
};
</script>

<template>
  <AlertDialog v-model:open="showDialog">
    <AlertDialogContent :class="contentClass">
      <AlertDialogHeader>
        <AlertDialogTitle>{{ title }}</AlertDialogTitle>
        <AlertDialogDescription>
          <slot name="description">{{ description }}</slot>
        </AlertDialogDescription>
      </AlertDialogHeader>

      <slot></slot>

      <!-- ✅ Footer now hides if `hideFooter` is true -->
      <AlertDialogFooter v-if="!hideFooter">
        <AlertDialogCancel @click="closeModal">{{ cancelText }}</AlertDialogCancel>
        <AlertDialogAction :variant="confirmVariant" @click="confirmAction">
          <slot name="confirm-text">{{ confirmText }}</slot>
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
