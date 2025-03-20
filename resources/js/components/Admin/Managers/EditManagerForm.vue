<template>
  <div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
    <form @submit.prevent="submit" class="bg-white p-6 rounded-lg shadow-md w-96">
      <h2 class="text-xl font-semibold mb-4 text-black">Edit User</h2>

      <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input 
          v-model="form.name" 
          id="name" 
          type="text" 
          class="w-full border-gray-300 rounded-md shadow-sm p-2 text-black"
          required 
        />
      </div>

      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input 
          v-model="form.email" 
          id="email" 
          type="email" 
          class="w-full border-gray-300 rounded-md shadow-sm p-2 text-black"
          required 
        />
      </div>

      <div class="flex justify-end">
        <button 
          type="button" 
          @click="$emit('close')" 
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded mr-2 hover:bg-gray-400"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
          Save Changes
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, defineProps, defineEmits } from "vue";

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
});

const form = reactive({ ...props.user });

const emit = defineEmits(["update", "close"]);

function submit() {
  emit("update", { ...form });
}
</script>
