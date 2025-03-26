<script setup>
import { ref, onMounted, h } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import { Button } from '@/components/ui/button';

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Manage Clients', href: '/dashboard/clients', active: true },
];

const clients = ref([]);
const pagination = ref({ pageIndex: 0, pageSize: 10, total: 0 });
const sorting = ref([]);
const filters = ref({ approved: false });
const columns = [
  { accessorKey: 'name', header: 'Client Name' },
  { accessorKey: 'email', header: 'Email' },
  { accessorKey: 'mobile', header: 'Mobile' },
  { accessorKey: 'country', header: 'Country' },
  { accessorKey: 'gender', header: 'Gender' },
  {
    accessorKey: 'Actions',
    header: 'Actions',
    cell: (info) => [
      h(Button, { variant: 'default', class: 'mx-1', onClick: () => openApproveModal(info.row.original) }, () => 'Approve'),
    ],
  },
];

const fetchClients = async () => {
  router.get('/dashboard/clients', {
    page: pagination.value.pageIndex + 1,
    perPage: pagination.value.pageSize,
    sorting: sorting.value,
    filters: filters.value,
  }, {
    preserveState: true,
    onSuccess: (page) => {
      clients.value = page.props.clients.data;
      pagination.value.total = page.props.clients.total;
    },
  });
};

const openApproveModal = (client) => {
  approveClient(client);
};

const approveClient = async (client) => {
  await router.post(`/dashboard/clients/${client.id}/approve`, {
    onSuccess: () => {
      fetchClients();
    },
  });
};

onMounted(fetchClients);
</script>

<template>
  <Head title="Manage Clients" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-6">
      <ManageDataTable
        title="Manage Clients"
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
      />
    </div>
  </AppLayout>
</template>
