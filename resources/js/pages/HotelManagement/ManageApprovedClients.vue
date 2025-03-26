<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'My Approved Clients', href: '/dashboard/approved', active: true },
];

const approvedClients = ref([]);
const pagination = ref({ pageIndex: 0, pageSize: 10, total: 0 });
const sorting = ref([]);
const filters = ref({ approved: true });
const columns = [
  { accessorKey: 'name', header: 'Client Name' },
  { accessorKey: 'email', header: 'Email' },
  { accessorKey: 'mobile', header: 'Mobile' },
  { accessorKey: 'country', header: 'Country' },
  { accessorKey: 'gender', header: 'Gender' },
];

const fetchApprovedClients = async () => {
  router.get('/dashboard/approved', {
    page: pagination.value.pageIndex + 1,
    perPage: pagination.value.pageSize,
    sorting: sorting.value,
    filters: filters.value,
  }, {
    preserveState: true,
    onSuccess: (page) => {
      approvedClients.value = page.props.approvedClients.data;
      pagination.value.total = page.props.approvedClients.total;
    },
  });
};

onMounted(fetchApprovedClients);
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
        @update:sorting="(newSorting) => { sorting = newSorting; fetchApprovedClients(); }"
        @update:pagination="(newPagination) => { pagination = newPagination; fetchApprovedClients(); }"
      />
    </div>
  </AppLayout>
</template>
