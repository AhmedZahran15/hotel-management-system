<template>
    <form @submit.prevent="submit">
      <div>
        <label for="name">Name</label>
        <input v-model="form.name" id="name" type="text" class="input" />
      </div>
      <div>
        <label for="email">Email</label>
        <input v-model="form.email" id="email" type="email" class="input" />
      </div>
      <div>
        <label for="country">Country</label>
        <input v-model="form.country" id="country" type="text" class="input" />
      </div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </template>
  
  <script setup>
  import { reactive } from 'vue';
  
  const form = reactive({
    name: '',
    email: '',
    country: '',
  });
  
  function submit() {
    console.log('Form submitted:', form);
  }
  </script>