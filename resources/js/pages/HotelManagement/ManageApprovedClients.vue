<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ManageDataTable from '@/components/Shared/ManageDataTable.vue'

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Approved Clients', href: route('clients.approved'), active: true },
];

const props = defineProps(['approved_clients']);
const params = new URLSearchParams(window.location.search);
const filters = ref({
    name: params.get('filter[name]') || '',
    email: params.get('filter[email]') || '',
});
const sorting = params.get('sort')? ref<SortingValue[]>([
    {
        id: params.get('sort')?.replace('-', '') || '',
        desc: params.get('sort')?.includes('-') || false,
    },
]): ref<SortingValue[]>([]);
const pagination = ref({
  pageIndex: props.clients?.meta?.current_page ? props.clients.meta.current_page - 1 : 0,
  pageSize: props.clients?.meta?.per_page || 10,
  dataSize: props.clients?.meta?.total || 0,
});

// Table Columns
const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'name', header: 'Client Name' },
    { accessorKey: 'email', header: 'Email' },
    {
        accessorKey: 'phones.0',
        header: 'Phone',
        cell: ({ row }) => (row.original.phones && row.original.phones.length > 0 ? row.original.phones[0] : 'N/A'),
    },
    { accessorKey: 'country.name', header: 'Country' },
    { accessorKey: 'gender', header: 'Gender' },
];

// Fetch Clients
const fetchApprovedClients = () => {
     const params = new URLSearchParams();
    // Apply filtering
    Object.entries(filters.value).forEach(([key, value]) => {
        if (value) params.append(`filter[${key}]`, value);
    });

    // Apply sorting
    if (sorting.value.length > 0) {
        const sortString = sorting.value
            .map((s: SortingValue) => (s.desc ? `-${s.id}` : s.id)) // Convert sorting object to query format
            .join(',');
        params.append('sort', sortString);
    }

    // Apply pagination
    if (pagination.value.pageIndex > 0)
    params.append('page', pagination.value.pageIndex + 1);

    router.get(route('clients.approved'), Object.fromEntries(params.entries()), {
        preserveScroll: true,
        preserveState: true,
        only: ['clients'],
        onSuccess: (response) => {
            pagination.value = {
                pageIndex: response.props.approved_clients.meta.current_page - 1,
                pageSize: response.props.approved_clients.meta.per_page,
                dataSize: response.props.approved_clients.meta.total,
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
