<template>
  <div class="p-6">
    <h1 class="mb-4 text-2xl font-bold text-black">Manage Clients</h1>

    <!-- Register Client Button -->
    <button
      @click="showRegisterForm = true"
      class="px-4 py-2 mb-4 text-white bg-gray-600 rounded hover:bg-gray-500"
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
import { ref, defineProps } from "vue";
import DataTable from "@/components/Shared/DataTable.vue";
import RegisterClientForm from "@/components/Admin/Clients/CreateClientForm.vue";
import { router } from '@inertiajs/vue3';

const props = defineProps(["clients"]);
const clients = ref(props.clients?.data??[]);

//Columns for DataTable
const columns = ref([
  { accessorKey: "id", header: "ID" },
  { accessorKey: "name", header: "Name" },
  { accessorKey: "email", header: "Email" },
  { accessorKey: "country", header: "Country" },
]);
const showRegisterForm = ref(false);
const addClient = (newClient) => {
//   const newId = clients.value.length ? Math.max(...clients.value.map((c) => c.id)) + 1 : 1;
//   clients.value.push({ ...newClient, id: newId });
router.post(route('clients.store'),newClient,
{
    preserveState: true,
        onSuccess: () => {
            selectedPost= {title:"",description:"",user_id:0};
            file = null;
            clearError();
        },
        onError:(errorMessagess)=>{
            console.log(errorMessagess);
        }
    });
  showRegisterForm.value = false;
};
</script>

<script>
import AdminLayout from "@/layouts/AdminLayout.vue";

export default {
  layout: AdminLayout,
};
</script>
