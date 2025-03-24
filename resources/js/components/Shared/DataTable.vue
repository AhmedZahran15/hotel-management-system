<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import ManageModal from "@/components/Shared/ManageModalEdit.vue";

// Props for columns and data
const props = defineProps<{
  columns: any[];
  data: any[];
}>();

// Emit events for edit and refresh
const emit = defineEmits(["edit", "refresh"]);

// State for delete confirmation modal
const showDeleteDialog = ref(false);
const selectedItem = ref<any>(null);

// State for notifications
const notification = ref<{ type: string; message: string } | null>(null);

// Confirm delete action
const confirmDelete = (item: any) => {
  selectedItem.value = item;
  showDeleteDialog.value = true;
};

// Delete manager via Axios
const deleteItem = async () => {
  if (!selectedItem.value) return;

  try {
    await axios.delete(`/dashboard/managers/${selectedItem.value.id}`);
    notification.value = { type: "success", message: "Manager deleted successfully!" };
    showDeleteDialog.value = false;
    emit("refresh");
  } catch (error) {
    notification.value = { type: "error", message: "Failed to delete manager. Please try again." };
    console.error("Error deleting manager:", error.response?.data || error.message);
  }
};

// Close notification
const closeNotification = () => {
  notification.value = null;
};
</script>

<template>
  <div>
    <!-- Notification -->
    <div v-if="notification" :class="`p-4 mb-4 rounded ${notification.type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`">
      <div class="flex justify-between items-center">
        <span>{{ notification.message }}</span>
        <button @click="closeNotification" class="text-lg font-bold">&times;</button>
      </div>
    </div>

    <!-- Table -->
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead v-for="column in columns" :key="column.accessorKey">{{ column.header }}</TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="row in data" :key="row.id">
          <TableCell v-for="column in columns" :key="column.accessorKey">
            <!-- Display Profile Image -->
            <template v-if="column.accessorKey === 'profile.img_name'">
              <img
                :src="row.profile?.img_name ? `/storage/${row.profile.img_name}` : '/storage/default-avatar.jpg'"
                class="w-10 h-10 rounded-full object-cover"
                alt="Manager Avatar"
              />
            </template>

            <!-- Display Other Fields -->
            <template v-else>
              {{ row[column.accessorKey] || "N/A" }}
            </template>
          </TableCell>
          
          <!-- Actions -->
          <TableCell>
            <div class="flex gap-2">
              <Button variant="secondary" size="sm" @click="$emit('edit', row)">Edit</Button>
              <Button variant="destructive" size="sm" @click="confirmDelete(row)">Delete</Button>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <!-- Delete Confirmation Modal -->
    <ManageModal
      :open="showDeleteDialog"
      title="Confirm Deletion"
      description="Are you sure you want to delete this manager? This action cannot be undone."
      @confirm="deleteItem"
      @update:open="showDeleteDialog = false"
    />
  </div>
</template>