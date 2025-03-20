<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4 text-black">Manage Receptionists</h1>

    <!-- Add Receptionist Button -->
    <button 
      @click="showCreateForm = true"
      class="mb-4 px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded"
    >
      Add Receptionist
    </button>

    <!-- Data Table -->
    <DataTable v-if="receptionists.length" :columns="columns" :data="receptionists" />
    <p v-else class="text-gray-500">No receptionists found.</p>

    <!-- Create Receptionist Form Modal -->
    <CreateReceptionistForm 
      v-if="showCreateForm" 
      @close="showCreateForm = false" 
      @create="addReceptionist" 
    />
  </div>
</template>

<script setup>
import { ref } from "vue";
import DataTable from "@/components/Shared/DataTable.vue";
import CreateReceptionistForm from "@/components/Admin/Receptionists/CreateReceptionistForm.vue";

//Receptionists Data
const receptionists = ref([
  {
    id: 1,
    name: "Emily Brown",
    email: "emily.brown@example.com",
    password: "********",
    national_id: "123456789",
    avatar_image: "https://via.placeholder.com/50",
    role: "Receptionist",
  },
  {
    id: 2,
    name: "Michael Green",
    email: "michael.green@example.com",
    password: "********",
    national_id: "987654321",
    avatar_image: "https://via.placeholder.com/50",
    role: "Receptionist",
  },
  {
    id: 3,
    name: "Sarah White",
    email: "sarah.white@example.com",
    password: "********",
    national_id: "456789123",
    avatar_image: "https://via.placeholder.com/50",
    role: "Receptionist",
  },
]);

//Columns for DataTable
const columns = ref([
  { accessorKey: "id", header: "ID" },
  { accessorKey: "name", header: "Name" },
  { accessorKey: "email", header: "Email" },
  { accessorKey: "national_id", header: "National ID" },
  { accessorKey: "avatar_image", header: "Avatar" },
  { accessorKey: "role", header: "Role" },
]);

const showCreateForm = ref(false);
const addReceptionist = (newReceptionist) => {
  const newId = receptionists.value.length ? Math.max(...receptionists.value.map((r) => r.id)) + 1 : 1;
  receptionists.value.push({ ...newReceptionist, id: newId, role: "Receptionist" });
  showCreateForm.value = false;
};
</script>

<script>
import AdminLayout from "@/layouts/AdminLayout.vue";

export default {
  layout: AdminLayout,
};
</script>
