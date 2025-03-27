<script setup>
import { ref, onMounted, h } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ManageDataTable from '@/components/Shared/ManageDataTable.vue'
import ManageModal from '@/components/Shared/ManageModal.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const { props } = usePage()
const countries = props.countries || []
const userRoles = props.auth.user.roles || []

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Manage Clients', href: '/dashboard/clients', active: true }
]

const clients = ref(props.clients.data || [])
const pagination = ref({ pageIndex: 0, pageSize: 10, total: props.clients.total || 0 })
const sorting = ref([])
const filters = ref({})
if(userRoles.includes('receptionist')){
  filters.value.approved = false
}

const defaultForm = () => ({
  id: null,
  name: '',
  email: '',
  country: '',
  gender: '',
  avatar_image: null,
  password: '',
  password_confirmation: ''
})
const form = ref(defaultForm())

const columns = [
  { accessorKey: 'id', header: 'ID' },
  { accessorKey: 'name', header: 'Client Name' },
  { accessorKey: 'email', header: 'Email' },
  { accessorKey: 'phones.0.phone', header: 'Mobile', cell: ({ row }) => row.original.phones && row.original.phones.length > 0 ? row.original.phones[0].phone : 'N/A' },
  { accessorKey: 'country.name', header: 'Country' },
  { accessorKey: 'gender', header: 'Gender' },
  {
    accessorKey: 'Actions',
    header: 'Actions',
    cell: (info) => {
      if(userRoles.includes('admin') || userRoles.includes('manager')){
        return [
          h(Button, { variant: 'default', class: 'mx-1', onClick: () => openEditModal(info.row.original) }, () => 'Edit'),
          h(Button, { variant: 'destructive', class: 'mx-1', onClick: () => openDeleteModal(info.row.original.id) }, () => 'Remove')
        ]
      } else if(userRoles.includes('receptionist')){
        if(info.row.original.approved_by === null){
          return [
            h(Button, { variant: 'default', class: 'bg-green-400 hover:bg-green-500 mx-1', onClick: () => approveClient(info.row.original) }, () => 'Approve')
          ]
        } else {
          return [
            h(Button, { variant: 'default', class: 'bg-gray-400 mx-1', disabled: true }, () => 'Approved')
          ]
        }
      }
      return []
    }
  }
]

function fetchClients() {
  router.get('/dashboard/clients', {
    page: pagination.value.pageIndex + 1,
    perPage: pagination.value.pageSize,
    sorting: sorting.value,
    filters: filters.value
  }, {
    preserveState: true,
    onSuccess: (page) => {
      console.log('Fetched Clients:', page.props.clients)
      clients.value = page.props.clients.data
      pagination.value.total = page.props.clients.total
    }
  })
}

function openEditModal(client) {
  let gender = client.gender ? client.gender.toLowerCase() : ''
  form.value = {
    id: client.id,
    name: client.name || '',
    email: client.email || '',
    country: typeof client.country === 'object' ? client.country.id : client.country,
    gender: gender,
    avatar_image: null,
    password: '',
    password_confirmation: ''
  }
  isEditModalOpen.value = true
}

function openDeleteModal(id) {
  selectedClientId.value = id
  isDeleteModalOpen.value = true
}

function handleFileUpload(event) {
  form.value.avatar_image = event.target.files.length ? event.target.files[0] : null
}

function resetAddForm() {
  form.value = defaultForm()
}

function resetEditForm() {
  form.value = defaultForm()
}

function handleAdd() {
  const formData = new FormData()
  Object.keys(form.value).forEach((key) => {
    if(form.value[key] !== null && form.value[key] !== ''){
      formData.append(key, form.value[key])
    }
  })
  router.post('/dashboard/clients', formData, {
    preserveState: true,
    onSuccess: () => {
      isAddModalOpen.value = false
      fetchClients()
      resetAddForm()
    },
    onError: (errors) => {
      console.log('Add Errors:', errors)
    }
  })
}

function handleEdit() {
  const formData = new FormData()
  formData.append('_method', 'PATCH')
  Object.keys(form.value).forEach((key) => {
    if(key !== 'id' && form.value[key] !== null && form.value[key] !== ''){
      formData.append(key, form.value[key])
    }
  })
  router.patch(`/dashboard/clients/${form.value.id}`, formData, {
    preserveState: true,
    onSuccess: () => {
      isEditModalOpen.value = false
      fetchClients()
    },
    onError: (errors) => {
      console.log('Edit Errors:', errors)
    }
  })
}

function confirmDelete() {
  router.delete(`/dashboard/clients/${selectedClientId.value}`, { preserveState: true, onSuccess: fetchClients })
  isDeleteModalOpen.value = false
}

function approveClient(client) {
  router.post(`/dashboard/clients/${client.id}/approve`, {}, {
    preserveState: true,
    onSuccess: () => {
      fetchClients()
    }
  })
}

onMounted(fetchClients)
</script>

<template>
  <Head title="Manage Clients" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-6">
      <ManageDataTable
        title="Clients"
        :columns="columns"
        :data="clients.map(client => ({
          ...client,
          country: typeof client.country === 'object' ? client.country : (countries.find(c => +c.id === +client.country) || { name: 'Unknown' })
        }))"
        :pagination="pagination"
        :manual-pagination="true"
        :manual-sorting="true"
        :manual-filtering="true"
        :sorting="sorting"
        @update:sorting="newSorting => { sorting = newSorting; fetchClients() }"
        @update:filters="newFilters => { filters = newFilters; fetchClients() }"
        @update:pagination="newPagination => { pagination = newPagination; fetchClients() }"
      >
        <template #table-action>
          <Button variant="default" @click="() => { isAddModalOpen = true; resetAddForm() }">Add Client</Button>
        </template>
      </ManageDataTable>

      <ManageModal v-if="isDeleteModalOpen" title="Deleting Client" v-model:open="isDeleteModalOpen" :buttonsVisible="false">
        <template #description>
          <p class="text-lg">Are you sure you want to delete this client?</p>
        </template>
        <template #footer>
          <Button variant="secondary" @click="isDeleteModalOpen = false">Cancel</Button>
          <Button variant="destructive" @click="confirmDelete">Delete</Button>
        </template>
      </ManageModal>

      <ManageModal v-if="isEditModalOpen" title="Edit Client" v-model:open="isEditModalOpen" :buttonsVisible="false" @update:open="val => { if (!val) resetEditForm() }">
        <template #description>
          <form class="flex flex-col gap-4 p-6" @submit.prevent="handleEdit">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" required />
            <Label for="email">Email</Label>
            <Input id="email" v-model="form.email" type="email" required />
            <Label for="country">Country</Label>
            <Select v-model="form.country">
              <SelectTrigger id="country" class="w-full">
                <SelectValue placeholder="Select a country" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="country in countries" :key="country.id" :value="country.id">
                  {{ country.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Label for="gender">Gender</Label>
            <div class="flex gap-4">
              <label><input type="radio" v-model="form.gender" value="male" required /> Male</label>
              <label><input type="radio" v-model="form.gender" value="female" required /> Female</label>
            </div>
            <Label for="avatar">Avatar</Label>
            <Input id="avatar" type="file" @change="handleFileUpload" />
            <div class="flex justify-end gap-2">
              <Button variant="secondary" @click="() => { isEditModalOpen = false; resetEditForm() }">Close</Button>
              <Button type="submit">Update</Button>
            </div>
          </form>
        </template>
      </ManageModal>

      <ManageModal v-if="isAddModalOpen" title="Add Client" v-model:open="isAddModalOpen" :buttonsVisible="false" @update:open="val => { if (!val) resetAddForm() }">
        <template #description>
          <form class="flex flex-col gap-4 p-6" @submit.prevent="handleAdd">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" required />
            <Label for="email">Email</Label>
            <Input id="email" v-model="form.email" type="email" required />
            <Label for="country">Country</Label>
            <Select v-model="form.country">
              <SelectTrigger id="country" class="w-full">
                <SelectValue placeholder="Select a country" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="country in countries" :key="country.id" :value="country.id">
                  {{ country.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Label for="gender">Gender</Label>
            <div class="flex gap-4">
              <label><input type="radio" v-model="form.gender" value="male" required /> Male</label>
              <label><input type="radio" v-model="form.gender" value="female" required /> Female</label>
            </div>
            <Label for="avatar">Avatar</Label>
            <Input id="avatar" type="file" @change="handleFileUpload" />
            <Label for="password">Password</Label>
            <Input id="password" type="password" v-model="form.password" required />
            <Label for="password_confirmation">Confirm Password</Label>
            <Input id="password_confirmation" type="password" v-model="form.password_confirmation" required />
            <div class="flex justify-end gap-2">
              <Button variant="secondary" @click="() => { isAddModalOpen = false; resetAddForm() }">Close</Button>
              <Button type="submit">Add</Button>
            </div>
          </form>
        </template>
      </ManageModal>
    </div>
  </AppLayout>
</template>
