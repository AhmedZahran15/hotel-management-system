<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import { Button } from '@/components/ui/button';

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Clients Reservations', href: '/dashboard/reservations', active: true },
];

const reservations = ref([]);
const pagination = ref({ pageIndex: 0, pageSize: 10, total: 0 });
const sorting = ref([]);
const filters = ref({ approved: true });
const columns = [
  { accessorKey: 'client.name', header: 'Client Name' },
  { accessorKey: 'accompany_number', header: 'Accompanying Guests' },
  { accessorKey: 'room.number', header: 'Room Number' },
  { accessorKey: 'reservation_price', header: 'Paid Price' },
];

const fetchReservations = async () => {
  router.get('/dashboard/reservations', {
    page: pagination.value.pageIndex + 1,
    perPage: pagination.value.pageSize,
    sorting: sorting.value,
    filters: filters.value,
  }, {
    preserveState: true,
    onSuccess: (page) => {
      reservations.value = page.props.reservations.data;
      pagination.value.total = page.props.reservations.total;
    },
  });
};

onMounted(fetchReservations);
</script>

<template>
  <Head title="Clients Reservations" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-6">
      <ManageDataTable
        title="Clients Reservations"
        :columns="columns"
        :data="reservations"
        :pagination="pagination"
        :manual-pagination="true"
        :manual-sorting="true"
        :manual-filtering="true"
        :sorting="sorting"
        @update:sorting="(newSorting) => { sorting = newSorting; fetchReservations(); }"
        @update:filters="(newFilters) => { filters = newFilters; fetchReservations(); }"
        @update:pagination="(newPagination) => { pagination = newPagination; fetchReservations(); }"
      >
      </ManageDataTable>
    </div>
  </AppLayout>
</template>
