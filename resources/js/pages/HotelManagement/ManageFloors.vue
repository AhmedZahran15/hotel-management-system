<script setup lang="ts">
import Alert from '@/components/Shared/Alert.vue';
import DataTable from '@/components/Shared/ManageDataTable.vue';
import Modal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { AlertCircle } from 'lucide-vue-next';
import { computed, defineProps, h, ref } from 'vue';

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
const filters = ref({
    number: params.get('filter[number]') || '',
    name: params.get('filter[name]') || '',
});
const sorting = ref<SortingValue[]>([]);
sorting.value = [
    {
        id: params.get('sort')?.replace('-', '') || '',
        desc: params.get('sort')?.includes('-') || false,
    },
];
const pagination = ref({
    pageIndex: props.floors.meta.current_page - 1,
    pageSize: props.floors.meta.per_page,
    dataSize: props.floors.meta.total,
});
//Columns for DataTable
const columns = ref<ColumnDef<Floor>[]>([
    { accessorKey: 'number', header: 'Floor Number' },
    { accessorKey: 'name', header: 'Floor Name' },
    { accessorKey: 'roomsCount', header: 'No of rooms' },
    { accessorKey: 'reservedRoomsCount', header: 'Reserved' },
    { accessorKey: 'availabledRoomsCount', header: 'Available' },
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
                              // if:info.row.original.manager.id  == page.props.auth.user.id || page.props.auth.user.roles[0] == "admin",
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
    params.append('perPage', pagination.value.pageSize);
    params.append('dataSize', pagination.value.dataSize);
    params.append('page', pagination.value.pageIndex + 1);

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

// Form data objects
const editForm = ref({
    floorName: '',
});

const addForm = ref({
    addFloorName: '',
});

// Create reset functions for forms
const resetAddForm = () => {
    addForm.value = {
        addFloorName: '',
    };
    // Clear any validation errors
    page.props.errors = {};
};

const resetEditForm = () => {
    // Clear any validation errors
    page.props.errors = {};
};

// Edit functionality
const editModalOpen = ref<boolean>(false);
const handleEdit = (floor: Floor) => {
    resetEditForm(); // Clear previous errors
    selectedFloor.value = floor;
    editModalOpen.value = true;

    // Set the form values
    editForm.value = {
        floorName: floor.name || '',
    };
};

// Add functionality
const addModalOpen = ref<boolean>(false);
const openAddModal = () => {
    resetAddForm(); // Reset form and clear errors
    addModalOpen.value = true;
};

const onEditSubmit = () => {
    if (selectedFloor.value) {
        router.put(
            route('floors.update', selectedFloor.value.number),
            {
                name: editForm.value.floorName,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    editModalOpen.value = false;
                    toast({ title: 'Floor updated successfully!' });
                    router.get(page.url);
                },
            },
        );
    }
};

const onAddSubmit = () => {
    router.post(
        route('floors.store'),
        {
            name: addForm.value.addFloorName,
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

// Close modal functions
const closeEditModal = () => {
    editModalOpen.value = false;
    resetEditForm();
};

const closeAddModal = () => {
    addModalOpen.value = false;
    resetAddForm();
};

//AlertDismiss
const dismissError = () => {
    page.props.errors = {};
};
</script>

<template>
    <Head title="Manage Floors" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl px-4 py-4">
            <!-- Errors -->
            <Alert
                class="mx-auto mt-4 w-10/12"
                v-for="(value, index) of errors"
                :key="index"
                :show="true"
                :variant="'destructive'"
                :title="index"
                :message="value"
            >
                <template v-slot:icon>
                    <AlertCircle class="h-4 w-4" />
                </template>
                <template v-slot:dismissBtn><Button :variant="'destructive'" @click="dismissError">dismiss</Button></template>
            </Alert>

            <!-- DataTable -->
            <div class="px-6">
                <DataTable
                    :columns="columns"
                    :data="props.floors.data"
                    :filters="filters"
                    :pagination="pagination"
                    :manual-pagination="true"
                    :manual-sorting="true"
                    :manual-filtering="true"
                    :sorting="sorting"
                    @update:sorting="
                        (newSorting) => {
                            sorting = newSorting;
                            //fetchData();
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
                            //fetchData();
                        }
                    "
                >
                    <template #table-action>
                        <Button @click="openAddModal" class="px-16">Add Floor</Button>
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
                @update:open="
                    (val) => {
                        if (!val) resetEditForm();
                    }
                "
            >
                <template #description>
                    <div class="flex flex-col justify-center gap-4 p-6">
                        <div>
                            <label class="text-sm font-medium">Floor Name</label>
                            <Input type="text" placeholder="Enter floor name" v-model="editForm.floorName" />
                            <p v-if="errors.name" class="text-sm text-red-500">{{ errors.name }}</p>
                        </div>
                        <div class="flex justify-end gap-2">
                            <Button variant="outline" @click="closeEditModal">Cancel</Button>
                            <Button @click="onEditSubmit">Update</Button>
                        </div>
                    </div>
                </template>
            </Modal>

            <!-- Add Modal -->
            <Modal
                v-if="addModalOpen"
                :title="'Enter the floor name:'"
                v-model:open="addModalOpen"
                :buttonsVisible="false"
                :disableEsc="false"
                @update:open="
                    (val) => {
                        if (!val) resetAddForm();
                    }
                "
            >
                <template #description>
                    <div class="flex flex-col justify-center gap-4 p-6">
                        <div>
                            <label class="text-sm font-medium">Floor Name</label>
                            <Input type="text" placeholder="Please write a descriptive name" v-model="addForm.addFloorName" />
                            <p v-if="errors.name" class="text-sm text-red-500">{{ errors.name }}</p>
                        </div>
                        <div class="flex justify-end gap-2">
                            <Button variant="outline" @click="closeAddModal">Cancel</Button>
                            <Button @click="onAddSubmit">Add</Button>
                        </div>
                    </div>
                </template>
            </Modal>
        </div>
    </AppLayout>
</template>
