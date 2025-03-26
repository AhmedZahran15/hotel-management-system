<script setup>
import { ref, onMounted, h } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { RadioGroupItem, RadioGroup } from '@/components/ui/radio-group';

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Manage Clients', href: '/dashboard/clients', active: true }
];

const clients = ref([]);
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedClientId = ref(null);
const pagination = ref({ pageIndex: 0, pageSize: 10, total: 0 });
const sorting = ref([]);
const filters = ref({});
const form = ref({
  id: null,
  name: '',
  email: '',
  country: '',
  gender: '',
  avatar_image: null,
  password: '',
  password_confirmation: '',
});

const columns = [
  { accessorKey: 'id', header: 'ID' },
  { accessorKey: 'name', header: 'Name' },
  { accessorKey: 'email', header: 'Email' },
  { accessorKey: 'country', header: 'Country' },
  { accessorKey: 'gender', header: 'Gender' },
  { 
    accessorKey: 'avatar_image', 
    header: 'Avatar', 
    cell: ({ row }) =>  
      h('img', { 
        src: row.original.avatar_image ? `/storage/${row.original.avatar_image}` : '/default-avatar.jpg', 
        alt: 'Avatar', 
        class: 'w-12 h-12 rounded-full object-cover' 
      })
  },
  {
    accessorKey: 'Actions',
    header: 'Actions',
    cell: (info) => [
      h(Button, { variant: 'default', class: 'mx-1', onClick: () => openEditModal(info.row.original) }, () => 'Edit'),
      h(Button, { variant: 'destructive', class: 'mx-1', onClick: () => openDeleteModal(info.row.original.id) }, () => 'Remove'),
    ],
  },
];

const fetchClients = async () => {
  router.get('/dashboard/clients', { 
    page: pagination.value.pageIndex + 1,
    perPage: pagination.value.pageSize,
    sorting: sorting.value,
    filters: filters.value
  }, {
    preserveState: true,
    onSuccess: (page) => {
      clients.value = page.props.clients.data;
      pagination.value.total = page.props.clients.total;
    }
  });
};

const openEditModal = (client) => {
  form.value = {
    id: client.id,
    name: client.name || '',
    email: client.email || '',
    country: client.country || '',
    gender: client.gender || '',
    avatar_image: null,
    password: '',
    password_confirmation: '',
  };
  isEditModalOpen.value = true;
};

const openDeleteModal = (id) => {
  selectedClientId.value = id;
  isDeleteModalOpen.value = true;
};

const handleFileUpload = (event) => {
  form.value.avatar_image = event.target.files[0];
};

const handleAdd = async () => {
  const formData = new FormData();
  formData.append('name', form.value.name);
  formData.append('email', form.value.email);
  formData.append('country', form.value.country);
  formData.append('gender', form.value.gender);
  formData.append('password', form.value.password);
  formData.append('password_confirmation', form.value.password_confirmation);

  if (form.value.avatar_image instanceof File) {
    formData.append('avatar_image', form.value.avatar_image);
  }

  await router.post('/dashboard/clients', formData, {
    onSuccess: () => {
      isAddModalOpen.value = false;
      fetchClients();
      form.value = {
        id: null,
        name: '',
        email: '',
        country: '',
        gender: '',
        avatar_image: null,
        password: '',
        password_confirmation: '',
      };
    },
  });
};

const handleEdit = async () => {
  const formData = new FormData();
  formData.append('_method', 'PATCH');
  formData.append('name', form.value.name);
  formData.append('email', form.value.email);
  formData.append('country', form.value.country);
  formData.append('gender', form.value.gender);

  if (form.value.password) {
    formData.append('password', form.value.password);
    formData.append('password_confirmation', form.value.password_confirmation);
  }

  if (form.value.avatar_image instanceof File) {
    formData.append('avatar_image', form.value.avatar_image);
  }

  await router.post(`/dashboard/clients/${form.value.id}`, formData, {
    onSuccess: () => {
      isEditModalOpen.value = false;
      fetchClients();
    },
  });
};

const confirmDelete = async () => {
  await router.delete(`/dashboard/clients/${selectedClientId.value}`, { preserveState: true, onSuccess: fetchClients });
  isDeleteModalOpen.value = false;
};

onMounted(fetchClients);
</script>

<template>
  <Head title="Manage Clients" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-6">
      <ManageDataTable
        title="Clients"
        :columns="columns"
        :data="clients"
        :pagination="pagination"
        :manual-pagination="true"
        :manual-sorting="true"
        :manual-filtering="true"
        :sorting="sorting"
        @update:sorting="(newSorting) => { sorting = newSorting; fetchClients(); }"
        @update:filters="(newFilters) => { filters = newFilters; fetchClients(); }"
        @update:pagination="(newPagination) => { pagination = newPagination; fetchClients(); }"
      >
        <template #table-action>
          <Button variant="default" @click="isAddModalOpen = true">Add Client</Button>
        </template>
      </ManageDataTable>

      <ManageModal 
        v-if="isDeleteModalOpen" 
        title="Deleting Client" 
        v-model:open="isDeleteModalOpen" 
        :buttonsVisible="false"
      >
        <template #description>
          <p class="text-lg">Are you sure you want to delete this client?</p>
        </template>

        <template #footer>
          <Button variant="secondary" @click="isDeleteModalOpen = false">Cancel</Button>
          <Button variant="destructive" @click="confirmDelete">Delete</Button>
        </template>
      </ManageModal>

      <ManageModal v-if="isEditModalOpen" title="Edit Client" v-model:open="isEditModalOpen" :buttonsVisible="false">
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
              <Label for="country">Country</Label>
              <Input id="country" v-model="form.country" required />
            </div>

            <div class="flex flex-col gap-1">
            <Label for="gender">Gender</Label>
            <RadioGroup v-model="form.gender" id="gender" class="flex items-center gap-4">
              <RadioGroupItem value="Male" id="male" class="mr-2" :checked="form.gender === 'Male'" />
              <Label for="male">Male</Label>
              <RadioGroupItem value="Female" id="female" class="mr-2" :checked="form.gender === 'Female'" />
              <Label for="female">Female</Label>
            </RadioGroup>
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

      <ManageModal v-if="isAddModalOpen" title="Add Client" v-model:open="isAddModalOpen" :buttonsVisible="false">
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
              <Label for="country">Country</Label>
              <Input id="country" v-model="form.country" required />
            </div>

            <div class="flex flex-col gap-1">
            <Label for="gender">Gender</Label>
            <RadioGroup v-model="form.gender" id="gender" class="flex items-center gap-4">
              <RadioGroupItem value="Male" id="male" class="mr-2" />
              <Label for="male">Male</Label>
              <RadioGroupItem value="Female" id="female" class="mr-2" />
              <Label for="female">Female</Label>
            </RadioGroup>
          </div>

            <div class="flex flex-col gap-1">
              <Label for="avatar">Avatar</Label>
              <Input id="avatar" type="file" @change="handleFileUpload" />
            </div>

            <div class="flex flex-col gap-1">
              <Label for="password">Password</Label>
              <Input id="password" type="password" v-model="form.password" required />
            </div>

            <div class="flex flex-col gap-1">
              <Label for="password_confirmation">Confirm Password</Label>
              <Input id="password_confirmation" type="password" v-model="form.password_confirmation" required />
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
