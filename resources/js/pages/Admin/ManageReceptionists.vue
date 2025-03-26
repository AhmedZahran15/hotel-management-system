<script setup>
import { ref, onMounted, h } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

const successMessage = ref('');
const errorMessage = ref('');
const loggedInUserId = ref(null);
const loggedInUserRole = ref('');

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Manage Receptionists', href: '/dashboard/receptionists', active: true }
];

const receptionists = ref([]);
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedReceptionistId = ref(null);
const pagination = ref({ pageIndex: 0, pageSize: 10, total: 0 });
const sorting = ref([]);
const filters = ref({});
const form = ref({ id: null, name: '', email: '', password: '', password_confirmation: '', national_id: '', avatar_image: null });


const { props } = usePage();
const user = props.auth.user;
const isAdmin = user?.roles.includes('admin');

const columns = [
  { accessorKey: 'id', header: 'ID' },
  { accessorKey: 'name', header: 'Name' },
  { accessorKey: 'email', header: 'Email' },
  {
    accessorKey: 'created_at',
    header: 'Created At',
    cell: (info) => new Date(info.getValue()).toLocaleString(),
  },
  ...(isAdmin
    ? [
        {
          accessorKey: 'creator',
          header: 'Manager Creator',
          cell: (info) => {
            const creator = info.getValue();
            return creator && creator.name ? creator.name : 'Unknown';
          },
        },
      ]
    : []),
  {
    accessorKey: 'Actions',
    header: 'Actions',
    cell: (info) => {
      const isAdmin = user?.roles.includes('admin');
      const isManagerAndCreator = user?.roles.includes('manager') && user.id === info.row.original.creator_user_id;

      return [
        h(Button, { 
          variant: 'default', 
          class: 'mx-1', 
          onClick: () => openEditModal(info.row.original),
          disabled: !(isAdmin || isManagerAndCreator)
        }, () => 'Edit'),
        h(Button, { 
          variant: 'destructive', 
          class: 'mx-1', 
          onClick: () => openDeleteModal(info.row.original.id),
          disabled: !(isAdmin || isManagerAndCreator)
        }, () => 'Remove'),
        info.row.original.banned_at
          ? h(Button, {
              variant: 'default',
              class: 'mx-1 bg-yellow-500 hover:bg-yellow-600 text-white',
              onClick: () => handleUnban(info.row.original.id),
              disabled: !(isAdmin || isManagerAndCreator)
            }, () => 'Unban')
          : h(Button, {
              variant: 'default',
              class: 'mx-1 bg-yellow-500 hover:bg-yellow-600 text-white',
              onClick: () => handleBan(info.row.original.id),
              disabled: !(isAdmin || isManagerAndCreator)
            }, () => 'Ban'),
      ];
    },
  },
];

const fetchReceptionists = async () => {
  router.get('/dashboard/receptionists', { 
    page: pagination.value.pageIndex + 1,
    perPage: pagination.value.pageSize,
    sorting: sorting.value,
    filters: filters.value
  }, {
    preserveState: true,
    onSuccess: (page) => {
      loggedInUserId.value = page.props.auth.user.id;
      loggedInUserRole.value = page.props.auth.user.roles[0]; 
      receptionists.value = page.props.receptionists.data;
      pagination.value.total = page.props.receptionists.total;  
     }
  });
};

const openEditModal = (receptionist) => {
  form.value = {
    id: receptionist.id,
    name: receptionist.name || '',
    email: receptionist.email || '',
    national_id: receptionist.profile?.national_id || '',
    avatar_image: null,
  };
  isEditModalOpen.value = true;
};

const openDeleteModal = (id) => {
  selectedReceptionistId.value = id;
  isDeleteModalOpen.value = true;
};

const handleFileUpload = (event) => {
  form.value.avatar_image = event.target.files[0];
};

const handleAdd = async () => {
  const formData = new FormData();
  formData.append('name', form.value.name);
  formData.append('email', form.value.email);
  formData.append('password', form.value.password);
  formData.append('password_confirmation', form.value.password_confirmation);
  formData.append('national_id', form.value.national_id);
  if (form.value.avatar_image instanceof File) {
    formData.append('avatar_image', form.value.avatar_image);
  }
  router.post('/dashboard/receptionists', formData, {
    onSuccess: () => {
      isAddModalOpen.value = false;
      fetchReceptionists();
    },
  });
};

const handleEdit = async () => {
  const formData = new FormData();
  formData.append('_method', 'PATCH');
  formData.append('name', form.value.name);
  formData.append('email', form.value.email);
  formData.append('national_id', form.value.national_id);

  if (form.value.avatar_image instanceof File) {
    formData.append('avatar_image', form.value.avatar_image);
  }

  router.post(`/dashboard/receptionists/${form.value.id}`, formData, {
    onSuccess: () => {
      isEditModalOpen.value = false;
      fetchReceptionists();
    },
  });
};

const confirmDelete = async () => {
  await router.delete(`/dashboard/receptionists/${selectedReceptionistId.value}`, { 
    preserveState: true, 
    onSuccess: fetchReceptionists 
  });
  isDeleteModalOpen.value = false;
};

const handleBan = async (id) => {
  try {
    await router.post(`/dashboard/receptionists/${id}/ban`);
    const updatedReceptionists = receptionists.value.map((receptionist) => {
      if (receptionist.id === id) {
        receptionist.banned_at = new Date();
        receptionist.is_banned = true;
      }
      return receptionist;
    });
    receptionists.value = updatedReceptionists;
    successMessage.value = 'Receptionist has been banned successfully.';
    errorMessage.value = '';
    setTimeout(() => {
      successMessage.value = '';
    }, 5000);
  } catch (error) {
    errorMessage.value = 'Failed to ban receptionist.';
    successMessage.value = '';
    setTimeout(() => {
      errorMessage.value = '';
    }, 5000);
  }
};

const handleUnban = async (id) => {
  try {
    await router.post(`/dashboard/receptionists/${id}/unban`);
    const updatedReceptionists = receptionists.value.map((receptionist) => {
      if (receptionist.id === id) {
        receptionist.banned_at = null;
        receptionist.is_banned = false;
      }
      return receptionist;
    });
    receptionists.value = updatedReceptionists;
    successMessage.value = 'Receptionist has been unbanned successfully.';
    errorMessage.value = '';
    setTimeout(() => {
      successMessage.value = '';
    }, 5000);
  } catch (error) {
    errorMessage.value = 'Failed to unban receptionist.';
    successMessage.value = '';
    setTimeout(() => {
      errorMessage.value = '';
    }, 5000);
  }
};

onMounted(fetchReceptionists);
</script>

<template>
    <Head title="Manage Receptionists" />
    <AppLayout :breadcrumbs="breadcrumbs">
      <div class="px-6">
        <div v-if="successMessage" class="bg-green-500 text-white p-4 rounded">
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="bg-red-500 text-white p-4 rounded">
          {{ errorMessage }}
        </div>

        <ManageDataTable
          title="Receptionists"
          :columns="columns"
          :data="receptionists"
          :pagination="pagination"
          :manual-pagination="true"
          :manual-sorting="true"
          :manual-filtering="true"
          :sorting="sorting"
          @update:sorting="(newSorting) => { sorting = newSorting; fetchReceptionists(); }"
          @update:filters="(newFilters) => { filters = newFilters; fetchReceptionists(); }"
          @update:pagination="(newPagination) => { pagination = newPagination; fetchReceptionists(); }"
        >
          <template #table-action>
            <Button variant="default" @click="isAddModalOpen = true">Add Receptionist</Button>
          </template>
        </ManageDataTable>

        <!-- Add Modal -->
        <ManageModal v-if="isAddModalOpen" title="Add Receptionist" v-model:open="isAddModalOpen" :buttonsVisible="false">
          <template #description>
            <form class="flex flex-col gap-4 p-6" @submit.prevent="handleAdd">
              <div class="flex flex-col gap-1">
                <Label for="name">Name</Label>
                <Input id="name" v-model="form.name" required />
              </div>
  
              <div class="flex flex-col gap-1">
                <Label for="email">Email</Label>
                <Input id="email" v-model="form.email" type="email" required />
              </div>
  
              
              <div class="flex flex-col gap-1">
                <Label for="national_id">National ID</Label>
                <Input id="national_id" v-model="form.national_id" required />
              </div>
              
              <div class="flex flex-col gap-1">
                <Label for="avatar">Avatar</Label>
                <Input id="avatar" type="file" @change="handleFileUpload" />
              </div>
              
              <div class="flex flex-col gap-1">
                <Label for="password">Password</Label>
                <Input id="password" v-model="form.password" type="password" required />
              </div>
  
              <div class="flex flex-col gap-1">
                <Label for="password_confirmation">Confirm Password</Label>
                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" required />
              </div>

              <div class="flex justify-end gap-2">
                <Button variant="secondary" @click="isAddModalOpen = false">Close</Button>
                <Button type="submit">Add</Button>
              </div>
            </form>
          </template>
          </ManageModal>

        <!-- Edit Modal -->
        <ManageModal v-if="isEditModalOpen" title="Edit Receptionist" v-model:open="isEditModalOpen" :buttonsVisible="false">
          <template #description>
            <form class="flex flex-col gap-4 p-6" @submit.prevent="handleEdit">
              <div class="flex flex-col gap-1">
                <Label for="name">Name</Label>
                <Input id="name" v-model="form.name" required />
              </div>

              <div class="flex flex-col gap-1">
                <Label for="email">Email</Label>
                <Input id="email" v-model="form.email" type="email" required />
              </div>

              <div class="flex flex-col gap-1">
                <Label for="national_id">National ID</Label>
                <Input id="national_id" v-model="form.national_id" required />
              </div>

              <div class="flex flex-col gap-1">
                <Label for="avatar">Avatar</Label>
                <Input id="avatar" type="file" @change="handleFileUpload" />
              </div>

              <div class="flex justify-end gap-2">
                <Button variant="secondary" @click="isEditModalOpen = false">Close</Button>
                <Button type="submit">Update</Button>
              </div>
            </form>
          </template>
        </ManageModal>

        <!-- Delete Modal -->
        <ManageModal 
        v-if="isDeleteModalOpen" 
        title="Deleting Receptionist" 
        v-model:open="isDeleteModalOpen" 
        :buttonsVisible="false"
        >
        <template #description>
            <p class="text-lg">Are you sure you want to delete this Receptionist?</p>
        </template>
        
        <template #footer>
            <Button variant="secondary" @click="isDeleteModalOpen = false">Cancel</Button>
            <Button variant="destructive" @click="confirmDelete">Delete</Button>
        </template>
        </ManageModal>
      </div>
    </AppLayout>
</template>
