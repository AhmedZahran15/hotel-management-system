<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ManageDataTable from '@/components/Shared/ManageDataTable.vue'

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'My Approved Clients', href: '/dashboard/approved', active: true }
]

const page = usePage()
const approvedClients = ref(page.props.approved_clients || [])
const pagination = ref({ pageIndex: 0, pageSize: 10, total: 0 })
const sorting = ref([])
const filters = ref({})

const columns = [
  { accessorKey: 'id', header: 'ID' },
  { accessorKey: 'name', header: 'Client Name' },
  { accessorKey: 'email', header: 'Email' },
  {
    accessorKey: 'phones.0.phone',
    header: 'Mobile',
    cell: ({ row }) => row.original.phones && row.original.phones.length > 0 ? row.original.phones[0].phone : 'N/A'
  },
  { accessorKey: 'country.name', header: 'Country' },
  { accessorKey: 'gender', header: 'Gender' }
]

const fetchApprovedClients = () => {
  router.get('/dashboard/approved', {
    page: 1,
    perPage: 100,
    sorting: sorting.value,
    filters: filters.value
  }, {
    preserveState: true,
    onSuccess: (page) => {
      approvedClients.value = page.props.approved_clients
    }
  })
}

onMounted(() => {
  fetchApprovedClients()
})
</script>

<template>
  <Head title="My Approved Clients" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-6">
      <ManageDataTable
        title="My Approved Clients"
        :columns="columns"
        :data="approvedClients"
        :pagination="pagination"
        :manual-pagination="true"
        :manual-sorting="true"
        :manual-filtering="true"
        :sorting="sorting"
        @update:sorting="newSorting => { sorting = newSorting; fetchApprovedClients() }"
        @update:filters="newFilters => { filters = newFilters; fetchApprovedClients() }"
        @update:pagination="newPagination => { pagination = newPagination; fetchApprovedClients() }"
      />
    </div>
  </AppLayout>
</template>
