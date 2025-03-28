<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clients Reservations', href: route('reservations.index')},
];

const reservations = ref([]);
const pagination = ref({ pageIndex: 0, pageSize: 10, total: 0 });
const columns = [
    { accessorKey: 'client.name', header: 'Client Name' },
    { accessorKey: 'accompany_number', header: 'Accompanying Guests' },
    { accessorKey: 'room.number', header: 'Room Number' },
    {
        accessorKey: 'reservation_price',
        header: 'Paid Price',
        cell: (info) => {
            const priceInCents = info.getValue();
            return `$${(priceInCents / 100).toFixed(2)}`;
        },
    },
];

const fetchReservations = () => {
    router.get(
        (route('reservations.index')),
        {
            page: pagination.value.pageIndex + 1,
            perPage: pagination.value.pageSize,
            sorting: sorting.value,
            filters: filters.value,
        },
        {
            preserveState: true,
            onSuccess: (page) => {
                reservations.value = page.props.reservations.data;
                pagination.value.total = page.props.reservations.total;
            },
        },
    );
};
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
                :filters="filters"
                :manual-pagination="true"
                :manual-sorting="true"
                :manual-filtering="true"
                :sorting="sorting"
                :showFilters="false"
                @update:sorting="
                    (newSorting) => {
                        sorting = newSorting;
                        fetchReservations();
                    }
                "
                @update:filters="
                    (newFilters) => {
                        filters = newFilters;
                        fetchReservations();
                    }
                "
                @update:pagination="
                    (newPagination) => {
                        pagination = newPagination;
                        fetchReservations();
                    }
                "
            >
            </ManageDataTable>
        </div>
    </AppLayout>
</template>
