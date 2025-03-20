<template>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4 text-black">Manage Managers</h1>
  
      <!-- Add Manager Button -->
      <button
        @click="showCreateForm = true"
        class="mb-4 px-4 py-2 text-white rounded bg-gray-600 hover:bg-gray-500  "
      >
        Add Manager
      </button>
  
      <!-- Data Table -->
      <DataTable :columns="columns" :data="managers" @delete="deleteManager" @edit="editManager" />
  
      <!-- Create Manager Form Modal -->
      <CreateManagerForm v-if="showCreateForm" @close="showCreateForm = false" @create="addManager" />
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from "vue";
  import { usePage } from "@inertiajs/vue3";
  import DataTable from "@/components/Shared/DataTable.vue";
  import CreateManagerForm from "@/components/Admin/Managers/CreateManagerForm.vue";
  
  const managers = ref(usePage().props.managers ?? []);

  const columns = [
    { accessorKey: "id", header: "ID" },
    { accessorKey: "name", header: "Name" },
    { accessorKey: "email", header: "Email" },
    { accessorKey: "national_id", header: "National ID" },
    { accessorKey: "role", header: "Role" },
    {
      accessorKey: "avatar_image",
      header: "Avatar",
      cell: ({ row }) =>
        row.original.avatar_image
          ? `<img src="${row.original.avatar_image}" class="w-10 h-10 rounded-full"/>`
          : "N/A",
    },
  ];

  const showCreateForm = ref(false);

  const addManager = (newManager) => {
    const newId = managers.value.length ? Math.max(...managers.value.map((m) => m.id)) + 1 : 1;
    managers.value.push({ ...newManager, id: newId, role: "Manager" });
    showCreateForm.value = false;
  };

  const editManager = (manager) => {
    console.log("Editing manager:", manager);
  };

  const deleteManager = (managerId) => {
    managers.value = managers.value.filter((m) => m.id !== managerId);
    console.log("Deleted manager with ID:", managerId);
  };
  </script>
  
  <script>
  import AdminLayout from "@/layouts/AdminLayout.vue";
  
  export default {
    layout: AdminLayout,
  };
  </script>
  