<template>
  <div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-xl font-bold mb-4 text-black">Add Receptionist</h2>
      <form @submit.prevent="submitForm">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <input 
            v-model="form.name" 
            type="text" 
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-black" 
            required 
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input 
            v-model="form.email" 
            type="email" 
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-black" 
            required 
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Password</label>
          <input 
            v-model="form.password" 
            type="password" 
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-black" 
            required 
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">National ID</label>
          <input 
            v-model="form.national_id" 
            type="text" 
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-black" 
            required 
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Avatar URL</label>
          <input 
            v-model="form.avatar_image" 
            type="url" 
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-black" 
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
            Add
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, defineEmits } from "vue";

const form = reactive({
  name: "",
  email: "",
  password: "",
  national_id: "",
  avatar_image: "",
});

const emit = defineEmits(["create", "close"]);

function submitForm() {
  emit("create", { ...form });
}
</script>
