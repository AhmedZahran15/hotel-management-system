<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ManageDataTable from '@/components/Shared/ManageDataTable.vue'
import {formulateURL, extractSorting} from '@/utils/helpers';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Approved Clients', href: route('clients.approved'), active: true },
];

const props = defineProps(['approved_clients']);
const params = new URLSearchParams(window.location.search);

const filters = ref([
    {column:"Name", value: params.get('filter[name]')||'', urlName: 'name'},
    {column:"Email", value: params.get('filter[email]')||'', urlName: 'email'},
    {column:"Country", value: params.get('filter[country]')||'', urlName: 'country'},
]);

const sorting = ref(extractSorting(params));

const pagination = ref({
    pageIndex: props.approved_clients.meta.current_page - 1,
    pageSize: props.approved_clients.meta.per_page,
    dataSize: props.approved_clients.meta.total,
});
// Table Columns
const columns = [
    { accessorKey: 'id', header: 'ID',sortable: true},
    { accessorKey: 'name', header: 'Client Name', sortable: true},
    { accessorKey: 'email', header: 'Email', sortable: true },
    { accessorKey: 'country.name', header: 'Country', sortable: true },
    {
        accessorKey: 'phones.0',
        header: 'Phone',
        cell: ({ row }) => (row.original.phones && row.original.phones.length > 0 ? row.original.phones[0] : 'N/A'),
    },
    { accessorKey: 'gender', header: 'Gender', sortable: true },
];

// Fetch Clients
const fetchApprovedClients = () => {
    const params = formulateURL(filters.value, sorting.value, pagination.value);

    router.get(route('clients.approved'), Object.fromEntries(params.entries()), {
        preserveScroll: true,
        preserveState: true,
        only: ['approved_clients'],
        onSuccess: () => {
            pagination.value = {
                pageIndex: props.approved_clients.meta.current_page - 1,
                pageSize: props.approved_clients.meta.per_page,
                dataSize: props.approved_clients.meta.total,
            };
        },
    });
};

</script>

<template>
    <Head title="My Approved Clients" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6">
            <ManageDataTable
                title="My Approved Clients"
                :columns="columns"
                :data="props.approved_clients.data"
                :pagination="pagination"
                :filters="filters"
                :manual-pagination="true"
                :manual-sorting="true"
                :manual-filtering="true"
                :sorting="sorting"
                @update:sorting="
                    (newSorting) => {
                        sorting = newSorting;
                        fetchApprovedClients();
                    }
                "
                @update:filters="
                    (newFilters) => {
                        filters = newFilters;
                        fetchApprovedClients();
                    }
                "
                @update:pagination="
                    (newPagination) => {
                        pagination = newPagination;
                        fetchApprovedClients();
                    }
                "
            />
        </div>
    </AppLayout>
</template>
