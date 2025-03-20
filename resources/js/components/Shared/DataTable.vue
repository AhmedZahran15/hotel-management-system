<template>
    <Card class="w-full overflow-hidden border border-gray-300 shadow-md rounded-lg bg-light text-black">
      <CardHeader>
        <h3 class="text-lg font-semibold text-black">Users Table</h3>
      </CardHeader>
      <CardContent>
        <Table class="w-full border-collapse">
          <TableHeader>
            <TableRow>
              <TableHead
                v-for="column in columns"
                :key="column.accessorKey"
                class="px-4 py-2 text-left font-medium"
              >
                <button @click="sortColumn(column.accessorKey)" class="flex items-center space-x-1 focus:outline-none">
                  <span>{{ column.header }}</span>
                  <span v-if="sortBy === column.accessorKey">
                    {{ sortOrder === 'asc' ? '▲' : '▼' }}
                  </span>
                </button>
              </TableHead>
              <TableHead class="px-4 py-2 text-left font-medium">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="row in paginatedData" :key="row.id" class="border-b last:border-none">
              <TableCell v-for="column in columns" :key="column.accessorKey" class="px-4 py-3">
                <template v-if="column.accessorKey === 'avatar_image'">
                  <img
                    v-if="row[column.accessorKey]"
                    :src="row[column.accessorKey]"
                    alt="Avatar"
                    class="w-10 h-10 rounded-full border border-gray-300"
                  />
                  <span v-else class="text-gray-500">N/A</span>
                </template>
                <template v-else>
                  {{ row[column.accessorKey] || 'N/A' }}
                </template>
              </TableCell>
              <TableCell class="px-4 py-3">
                <Button variant="outline" class="text-white mr-2" @click="editUser(row)">
                  Edit
                </Button>
                <Button variant="destructive" @click="confirmDelete(row.id)">
                  Delete
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
  
        <!-- Pagination Controls -->
        <div class="flex justify-between items-center mt-4 px-4">
          <Button @click="prevPage" :disabled="currentPage === 1">◀ Previous</Button>
          <span>Page {{ currentPage }} of {{ totalPages }}</span>
          <Button @click="nextPage" :disabled="currentPage >= totalPages">Next ▶</Button>
        </div>
      </CardContent>
  
      <!-- Edit Form Modal -->
      <EditForm v-if="selectedUser" :user="selectedUser" @update="updateUser" @close="selectedUser = null" />
  
      <!-- Delete Confirmation Modal -->
      <div v-if="deleteUserId" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded shadow-lg w-96">
          <h2 class="text-xl font-bold mb-4 text-black">Confirm Deletion</h2>
          <p class="mb-4 text-gray-700">Are you sure you want to delete this user?</p>
          <div class="flex justify-end">
            <button
              type="button"
              @click="deleteUserId = null"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded mr-2 hover:bg-gray-400"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="deleteUser"
              class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            >
              Confirm
            </button>
          </div>
        </div>
      </div>
    </Card>
  </template>
  
  <script setup>
  import { ref, computed } from "vue";
  import { Button } from "@/components/ui/button";
  import { Card, CardHeader, CardContent } from "@/components/ui/card";
  import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/table";
  import EditForm from "@/components/Admin/Managers/EditManagerForm.vue";
  const props = defineProps({
    columns: {
      type: Array,
      required: true,
    },
    data: {
      type: Array,
      required: true,
    },
  });
  
  //Sorting
  const sortBy = ref(null);
  const sortOrder = ref("asc");
  
  const sortedData = computed(() => {
    if (!sortBy.value) return props.data;
  
    return [...props.data].sort((a, b) => {
      const valueA = a[sortBy.value];
      const valueB = b[sortBy.value];
  
      if (typeof valueA === "string") {
        return sortOrder.value === "asc"
          ? valueA.localeCompare(valueB)
          : valueB.localeCompare(valueA);
      }
      return sortOrder.value === "asc" ? valueA - valueB : valueB - valueA;
    });
  });
  
  const sortColumn = (column) => {
    if (sortBy.value === column) {
      sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
    } else {
      sortBy.value = column;
      sortOrder.value = "asc";
    }
  };
  
  //Pagination
  const currentPage = ref(1);
  const itemsPerPage = 5;
  
  const totalPages = computed(() =>
    Math.ceil(sortedData.value.length / itemsPerPage)
  );
  
  const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return sortedData.value.slice(start, end);
  });
  
  const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
  };
  
  const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
  };
  const selectedUser = ref(null);
  
  const editUser = (user) => {
    selectedUser.value = { ...user };
  };
  
  const updateUser = (updatedUser) => {
    const index = props.data.findIndex((u) => u.id === updatedUser.id);
    if (index !== -1) {
      props.data[index] = updatedUser;
    }
    selectedUser.value = null;
  };
  const deleteUserId = ref(null);
  
  const confirmDelete = (userId) => {
    deleteUserId.value = userId;
  };
  
  const deleteUser = () => {
    if (deleteUserId.value !== null) {
      const index = props.data.findIndex((u) => u.id === deleteUserId.value);
      if (index !== -1) {
        props.data.splice(index, 1);
      }
      deleteUserId.value = null;
    }
  };
  </script>
  