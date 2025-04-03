<script setup lang="ts">
import Form from '@/components/Shared/Form.vue';
import DataTable from '@/components/Shared/ManageDataTable.vue';
import Modal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { toast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formulateURL, extractSorting } from '@/utils/helpers.ts';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, defineProps, h, ref } from 'vue';
import * as z from 'zod';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Floors',
        href: route('floors.index'),
    },
];

const page = usePage();
const props = defineProps(['floors']);
const errors = computed(() => page.props.errors);
const params = new URLSearchParams(window.location.search);
const filters = ref([
    {column:"Floor Number", value: params.get('filter[number]')||'', urlName: 'number'},
    {column:"Floor Name", value: params.get('filter[name]')||'', urlName: 'name'},
]);

const sorting = ref(extractSorting(params));

const pagination = ref({
    pageIndex: props.floors.meta.current_page - 1,
    pageSize: props.floors.meta.per_page,
    dataSize: props.floors.meta.total,
});
//Columns for DataTable
const columns = ref<ColumnDef<Floor>[]>([
    { accessorKey: 'number', header: 'Floor Number',sortable: true },
    { accessorKey: 'name', header: 'Floor Name',sortable: true },
    { accessorKey: 'rooms_count', header: 'No of rooms',sortable: true },
    { accessorKey: 'reservedRoomsCount', header: 'Reserved',sortable: false },
    { accessorKey: 'availabledRoomsCount', header: 'Available',sortable: false },
    {
        accessorKey: 'Edit',
        header: 'Actions',
        cell: (info: any) =>
            info.row.original.manager_id == page.props.auth.user.id || page.props.auth.user.roles[0] == 'admin'
                ? [
                    h(Button, { variant: 'default', class: 'mx-1', onClick: () => handleEdit(info.row.original) }, () => 'Edit'),
                    h(
                        Button,
                        {
                            variant: 'destructive',
                            class: 'mx-1',
                            disabled: info.row.original.roomsCount != 0,
                            onClick: () => handleDelete(info.row.original),
                        },
                        () => 'Remove',
                    ),
                ]
                : '',
    },
]);

//append Manger column in case of Admin (Depending on the data sent from the backend)
if (props.floors?.data?.length > 0 && props.floors.data[0]?.manager) {
    columns.value.splice(2, 0, { accessorKey: 'manager.name', header: 'Manager' });
}

const fetchData = (url?: string) => {
    const params = formulateURL(filters.value, sorting.value, pagination.value);

    // Fetch data with updated parameters
    router.get(url || route('floors.index'), Object.fromEntries(params.entries()), {
        preserveScroll: true,
        preserveState: true,
        only: ['floors'],
        onSuccess: () => {
            pagination.value = {
                pageIndex: props.floors.meta.current_page - 1,
                pageSize: props.floors.meta.per_page,
                dataSize: props.floors.meta.total,
            };
        },
    });
};

// Delete
const selectedFloor = ref<Floor | null>(null);

const deleteModalOpen = ref<boolean>(false);
const handleDelete = (floor: Floor) => {
    selectedFloor.value = floor;
    deleteModalOpen.value = true;
};
const deleteConfirmed = () => {
    if (selectedFloor.value) {
        router.delete(route('floors.destroy', selectedFloor.value.number));
        router.get(page.url);
    }
    selectedFloor.value = null;
};

//form validation
const FormSchema = z.object({
    FloorName: z.string().min(2, 'Floor name is required').max(50, 'Too long').describe('Floor name'),
});

// Edit functionality
const editModalOpen = ref<boolean>(false);

// Inetial values for edit form
const editForm = ref({
    floorName: '',
});

const handleEdit = (floor: Floor) => {
    selectedFloor.value = floor;
    editForm.value['FloorName'] = floor.name;
    editModalOpen.value = true;
};

const onEditSubmit = (formValues: any) => {
    if (selectedFloor.value) {
        router.put(
            route('floors.update', selectedFloor.value.number),
            {
                name: formValues.FloorName,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    editModalOpen.value = false;
                    toast({ title: 'Floor updated successfully!' });
                },
            },
        );
    }
};

// Add functionality
const addModalOpen = ref<boolean>(false);
const onAddSubmit = (formValues: any) => {
    router.post(
        route('floors.store'),
        {
            name: formValues.FloorName,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                addModalOpen.value = false;
                toast({ title: 'Floor added successfully!' });
                router.get(page.url);
            },
        },
    );
};
</script>

<template>
    <Head title="Manage Floors" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl px-4 py-4">
            <!-- DataTable -->
            <div class="px-6">
                <DataTable
                    :columns="columns"
                    :data="props.floors.data"
                    :filters="filters"
                    :pagination="pagination"
                    :errors="errors"
                    :manual-pagination="true"
                    :manual-sorting="true"
                    :manual-filtering="true"
                    :sorting="sorting"
                    @update:sorting="
                        (newSorting) => {
                            sorting = newSorting;
                            fetchData();
                        }
                    "
                    @update:filters="
                        (newFilters) => {
                            filters = newFilters;
                            fetchData();
                        }
                    "
                    @update:pagination="
                        (newPagination) => {
                            pagination = newPagination;
                            fetchData();
                        }
                    "
                >
                    <template #table-action>
                        <Button @click="addModalOpen = true" class="px-16">Add Floor</Button>
                    </template>
                </DataTable>
            </div>
            <!-- Delete Modal -->
            <Modal
                v-if="deleteModalOpen"
                :title="'Deleting floor no. ' + selectedFloor?.number.toString()"
                v-model:open="deleteModalOpen"
                @confirm="deleteConfirmed"
            >
                <template #description>
                    <div class="flex flex-col gap-2 py-3">
                        <p class="text-lg">Are you sure you want ro delete this floor?</p>
                        <p class="text-xs">Note: Can't delete floor containing floors</p>
                    </div>
                </template>
            </Modal>

            <!-- Edit Modal -->
            <Modal
                v-if="editModalOpen"
                :title="'Updating floor no. ' + selectedFloor?.number.toString()"
                v-model:open="editModalOpen"
                :buttonsVisible="false"
                :disableEsc="false"
                :errors="errors"
            >
                <template #description>
                    <Form
                        :schema="FormSchema"
                        :initialValues="editForm"
                        :submitText="'Update'"
                        @submit="onEditSubmit($event)"
                        @cancel="editModalOpen = false"
                    />
                </template>
            </Modal>

            <!-- Add Modal -->
            <Modal
                v-if="addModalOpen"
                :title="'Enter the floor name:'"
                v-model:open="addModalOpen"
                :buttonsVisible="false"
                :disableEsc="false"
                :errors="errors"
            >
                <template #description>
                    <Form :schema="FormSchema" :submitText="'Add'" :initialValues="{}" @submit="onAddSubmit($event)" @cancel="AddModalOpen = false" />
                </template>
            </Modal>
        </div>
    </AppLayout>
</template>
