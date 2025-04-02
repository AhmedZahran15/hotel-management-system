<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import { formulateURL, extractSorting } from '@/utils/helpers.ts';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clients Reservations', href: route('reservations.index')},
];

const props = defineProps(['reservations']);
const params = new URLSearchParams(window.location.search);
const filters = ref([
    {column:"Client Name", value: params.get('filter[client.name]')||'', urlName: 'client.name'},
    {column:"Accompanying Guests", value: params.get('filter[accompany_number]')||'', urlName: 'accompany_number'},
    {column:"Room Number", value: params.get('filter[room_number]')||'', urlName: 'room_number'},
]);

const sorting = ref(extractSorting(params));

const pagination = ref({
    pageIndex: props.reservations.current_page - 1,
    pageSize: props.reservations.per_page,
    dataSize: props.reservations.total,
});

const columns = [
    { accessorKey: 'client.name', header: 'Client Name', sortable:true },
    { accessorKey: 'accompany_number', header: 'Accompanying Guests', sortable:true },
    { accessorKey: 'room_number', header: 'Room Number', sortable:true },
    {
        accessorKey: 'reservation_price',
        header: 'Paid Price',
        sortable:true,
        cell: (info) => {
            const priceInCents = info.getValue();
            return `$${(priceInCents / 100).toFixed(2)}`;
        },
    },
];

const fetchReservations = () => {
    //filters.value.reservation_price = filters.value.reservation_price ? filters.value.reservation_price * 100 : null;
    const params = formulateURL(filters.value, sorting.value, pagination.value);
    console.log(params.toString());
    router.get(
        route('reservations.index'),Object.fromEntries(params.entries()),
        {
            page: pagination.value.pageIndex + 1,
            perPage: pagination.value.pageSize,
        },
        {
            preserveState: true,
            onSuccess: () => {
                pagination.value = {
                    pageIndex: props.reservations.current_page - 1,
                    pageSize: props.reservations.per_page,
                    dataSize: props.reservations.total,
                };
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
                :data="props.reservations.data"
                :pagination="pagination"
                :sorting="sorting"
                :filters="filters"
                :manual-pagination="true"
                :manual-sorting="true"
                :manual-filtering="true"
                :showFilters="false"
                @update:sorting="(newSorting) => {
                    sorting = newSorting;
                    fetchReservations();}"
                @update:filters=" (newFilters) => {
                    filters = newFilters;
                    fetchReservations();}"
                @update:pagination="(newPagination) => {
                    pagination = newPagination;
                    fetchReservations();}"
                >
            </ManageDataTable>
        </div>
    </AppLayout>
</template>
