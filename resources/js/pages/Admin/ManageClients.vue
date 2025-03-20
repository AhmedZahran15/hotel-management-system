<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4 text-black">Manage Clients</h1>

    <!-- Register Client Button -->
    <button 
      @click="showRegisterForm = true"
      class="mb-4 px-4 py-2 text-white rounded bg-gray-600 hover:bg-gray-500"
    >
      Register Client
    </button>

    <DataTable v-if="clients.length" :columns="columns" :data="clients" />
    <p v-else class="text-gray-500">No clients found.</p>

    <!-- Register Client Form Modal -->
    <RegisterClientForm 
      v-if="showRegisterForm" 
      @close="showRegisterForm = false" 
      @register="addClient" 
    />
  </div>
</template>

<script setup>
import { ref } from "vue";
import DataTable from "@/components/Shared/DataTable.vue";
import RegisterClientForm from "@/components/Admin/Clients/CreateClientForm.vue";

//Clients Data
const clients = ref([
  { id: 1, name: "Tom Wilson", email: "tom.wilson@example.com", country: "USA" },
  { id: 2, name: "Anna Taylor", email: "anna.taylor@example.com", country: "UK" },
  { id: 3, name: "David Lee", email: "david.lee@example.com", country: "Canada" },
]);

//Columns for DataTable
const columns = ref([
  { accessorKey: "id", header: "ID" },
  { accessorKey: "name", header: "Name" },
  { accessorKey: "email", header: "Email" },
  { accessorKey: "country", header: "Country" },
]);
const showRegisterForm = ref(false);
const addClient = (newClient) => {
  const newId = clients.value.length ? Math.max(...clients.value.map((c) => c.id)) + 1 : 1;
  clients.value.push({ ...newClient, id: newId });
  showRegisterForm.value = false;
};
</script>

<script>
import AdminLayout from "@/layouts/AdminLayout.vue";

export default {
  layout: AdminLayout,
};
</script>
