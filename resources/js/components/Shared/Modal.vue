<script setup lang="ts">
import { defineProps, defineEmits } from "vue";

defineProps({
  isOpen: Boolean,
  title: { type: String, default: "Modal Title" },
  showFooter: { type: Boolean, default: true },
  confirmText: { type: String, default: null },
});

const emit = defineEmits(["close", "confirm"]);
</script>

<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
        <!-- Header -->
        <div class="flex justify-between items-center border-b px-4 py-2">
          <h3 class="text-lg font-semibold">{{ title }}</h3>
          <button @click="emit('close')" class="text-gray-500 hover:text-gray-700 text-xl font-bold">
            &times;
          </button>
        </div>

        <!-- Body -->
        <div class="p-4">
          <slot></slot>
        </div>

        <!-- Footer -->
        <div v-if="showFooter" class="flex justify-end border-t px-4 py-2">
          <button
            v-if="confirmText"
            @click="emit('confirm')"
            class="ml-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
