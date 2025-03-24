<script setup lang="ts">
import ManagerForm from '@/components/Admin/Managers/ManagerForm.vue';
import DataTable from '@/components/Shared/DataTable.vue';
import ManageModal from '@/components/Shared/ManageModalEdit.vue';
import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

// ✅ Fetch managers from Inertia
const page = usePage();
const managers = ref(page.props.managers?.data || []);
const totalPages = computed(() => page.props.managers?.last_page || 1);

// ✅ State for Modals
const showModal = ref(false);
const editingManager = ref(null);
const showDeleteDialog = ref(false);
const managerToDelete = ref(null);

// ✅ Fetch Updated Manager Data (Table Refresh)
const refreshManagers = async () => {
    try {
        const response = await axios.get('/dashboard/managers');
        managers.value = response.data.data; // ✅ Update state dynamically
    } catch (error) {
        console.error('❌ Error fetching managers:', error);
    }
};

// ✅ Open Modal for Create/Edit
const openModal = (manager = null) => {
    editingManager.value = manager;
    showModal.value = true;
};

// ✅ Open Delete Confirmation
const confirmDelete = (manager) => {
    managerToDelete.value = manager;
    showDeleteDialog.value = true;
};

// ✅ Delete Manager via Axios & Update State
const deleteManager = async () => {
    try {
        await axios.delete(`/dashboard/managers/${managerToDelete.value.id}`);
        alert('✅ Manager deleted successfully!');
        showDeleteDialog.value = false;

        // ✅ Remove the deleted manager from the table (No reload)
        managers.value = managers.value.filter((m) => m.id !== managerToDelete.value.id);
    } catch (error) {
        console.error('❌ Error deleting manager:', error.response?.data || error.message);
        alert('❌ Failed to delete manager.');
    }
};
</script>

<template>
    <AppSidebarLayout>
        <div>
            <h2 class="mb-4 text-xl font-bold">Manage Managers</h2>

            <!-- ✅ Add Manager Button -->
            <Button variant="secondary" @click="openModal()" class="mb-4">+ Add Manager</Button>

            <!-- ✅ Data Table -->
            <DataTable
                :columns="[
                    { accessorKey: 'id', header: 'ID', sortable: true },
                    { accessorKey: 'name', header: 'Name', sortable: true },
                    { accessorKey: 'email', header: 'Email', sortable: true },
                    { accessorKey: 'profile.national_id', header: 'National ID', sortable: true },
                    { accessorKey: 'profile.img_name', header: 'Avatar' },
                ]"
                :data="managers"
                :totalPages="totalPages"
                @edit="openModal"
                @delete="confirmDelete"
            />

            <!-- ✅ Manager Form Modal -->
            <ManageModal :open="showModal" :title="editingManager ? 'Edit Manager' : 'Create Manager'" @update:open="showModal = false">
                <ManagerForm :manager="editingManager" @close="showModal = false" @refresh="refreshManagers" />
            </ManageModal>
        </div>
    </AppSidebarLayout>
</template>
