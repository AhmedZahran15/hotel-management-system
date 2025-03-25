<script setup>
import { ref, onMounted, h } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

const breadcrumbs = [
  { label: 'Dashboard', url: '/dashboard' },
  { label: 'Manage Managers', url: '/dashboard/managers', active: true }
];

const managers = ref([]);
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedManagerId = ref(null);
const pagination = ref({ pageIndex: 0, pageSize: 10, total: 0 });
const sorting = ref([]);
const filters = ref({});
const form = ref({ id: null, name: '', email: '', password: '', password_confirmation: '', national_id: '', avatar_image: null });

const columns = [
  { accessorKey: 'id', header: 'ID' },
  { accessorKey: 'name', header: 'Name' },
  { accessorKey: 'email', header: 'Email' },
  { accessorKey: 'profile.national_id', header: 'National ID' },
  { 
    accessorKey: 'avatar_image', 
    header: 'Avatar', 
    cell: ({ row }) =>  
      h('img', { 
        src: row.original.profile?.img_name ? `/storage/${row.original.profile.img_name}` : '/default-avatar.jpg', 
        alt: 'Avatar', 
        class: 'w-12 h-12 rounded-full object-cover' 
      })
  },
  {
    accessorKey: 'Edit',
    header: 'Actions',
    cell: (info) => [
      h(Button, { variant: 'default', class: 'mx-1', onClick: () => openEditModal(info.row.original) }, () => 'Edit'),
      h(
        Button,
        {
          variant: 'destructive',
          class: 'mx-1',
          onClick: () => openDeleteModal(info.row.original.id),
        },
        () => 'Remove',
      ),
    ],
  },
];

const fetchManagers = async () => {
  router.get('/dashboard/managers', { 
    page: pagination.value.pageIndex + 1,
    perPage: pagination.value.pageSize,
    sorting: sorting.value,
    filters: filters.value
  }, {
    preserveState: true,
    onSuccess: (page) => {
      managers.value = page.props.managers.data;
      pagination.value.total = page.props.managers.total;
    }
  });
};

const openEditModal = (manager) => {
  form.value = {
    id: manager.id,
    name: manager.name || '',
    email: manager.email || '',
    national_id: manager.profile?.national_id || '',
    avatar_image: null,
  };
  isEditModalOpen.value = true;
};

const openDeleteModal = (id) => {
  selectedManagerId.value = id;
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
  formData.append('password_confirmation', form.value.password);
  formData.append('national_id', form.value.national_id);
  if (form.value.avatar_image instanceof File) {
    formData.append('avatar_image', form.value.avatar_image);
  }
  router.post('/dashboard/managers', formData, {
    onSuccess: () => {
      isAddModalOpen.value = false;
      fetchManagers();
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

  router.post(`/dashboard/managers/${form.value.id}`, formData, {
    onSuccess: () => {
      isEditModalOpen.value = false;
      fetchManagers();
    },
  });
};

const confirmDelete = async () => {
  await router.delete(`/dashboard/managers/${selectedManagerId.value}`, { preserveState: true, onSuccess: fetchManagers });
  isDeleteModalOpen.value = false;
};
onMounted(fetchManagers);
</script>
<template>
    <Head title="Manage Managers" />
    <AppLayout :breadcrumbs="breadcrumbs">
      <div class="px-6">
        <ManageDataTable
          title="Managers"
          :columns="columns"
          :data="managers"
          :pagination="pagination"
          :manual-pagination="true"
          :manual-sorting="true"
          :manual-filtering="true"
          :sorting="sorting"
          @update:sorting="(newSorting) => { sorting = newSorting; fetchManagers(); }"
          @update:filters="(newFilters) => { filters = newFilters; fetchManagers(); }"
          @update:pagination="(newPagination) => { pagination = newPagination; fetchManagers(); }"
        >
          <template #table-action>
            <Button variant="default" @click="isAddModalOpen = true">Add Manager</Button>
          </template>
        </ManageDataTable>
  
        <!-- Delete Modal -->
        <ManageModal 
        v-if="isDeleteModalOpen" 
        title="Deleting Manager" 
        v-model:open="isDeleteModalOpen" 
        :buttonsVisible="false"
        >
        <template #description>
            <p class="text-lg">Are you sure you want to delete this manager?</p>
        </template>
        
        <template #footer>
            <Button variant="secondary" @click="isDeleteModalOpen = false">Cancel</Button>
            <Button variant="destructive" @click="confirmDelete">Delete</Button>
        </template>
        </ManageModal>

  
        <!-- Edit Modal -->
        <ManageModal v-if="isEditModalOpen" title="Edit Manager" v-model:open="isEditModalOpen" :buttonsVisible="false">
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
  
        <!-- Add Modal -->
        <ManageModal v-if="isAddModalOpen" title="Add Manager" v-model:open="isAddModalOpen" :buttonsVisible="false">
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
                <Label for="password">Password</Label>
                <Input id="password" v-model="form.password" type="password" required />
              </div>
  
              <div class="flex flex-col gap-1">
                <Label for="password_confirmation">Confirm Password</Label>
                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" required />
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
                <Button variant="secondary" @click="isAddModalOpen = false">Close</Button>
                <Button type="submit">Add</Button>
              </div>
            </form>
          </template>
        </ManageModal>
      </div>
    </AppLayout>
  </template>
