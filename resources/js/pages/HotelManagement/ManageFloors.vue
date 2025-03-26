<script setup lang="ts">
import Alert from '@/components/Shared/Alert.vue';
import DataTable from '@/components/Shared/ManageDataTable.vue';
import Modal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { toTypedSchema } from '@vee-validate/zod';
import { AlertCircle } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { computed, defineProps, h, ref, watch } from 'vue';
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

//Edit
const editModalOpen = ref<boolean>(false);
const handleEdit = (floor: Floor) => {
    selectedFloor.value = floor;
    editModalOpen.value = true;
};

// Watch for when edit modal opens to initialize form
watch(editModalOpen, (newVal) => {
    console.log('edit modal open:', newVal);
    if (newVal && selectedFloor.value) {
        editForm.resetForm({
            values: {
                floorName: selectedFloor.value.name || '',
            },
        });
    }
});

const editFormSchema = toTypedSchema(
    z.object({
        floorName: z.string().min(2, 'Floor name is required').max(50, 'Too long'),
    }),
);

const editForm = useForm({
    validationSchema: editFormSchema,
    initialValues: {
        floorName: '',
    },
});

const onEditSubmit = editForm.handleSubmit((values: any) => {
    if (selectedFloor.value) {
        router.put(
            route('floors.update', selectedFloor.value.number),
            {
                name: values.floorName,
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
});

//Add
const addModalOpen = ref<boolean>(false);

// Reset add form when modal opens
watch(addModalOpen, (newVal) => {
    if (newVal) {
        addForm.resetForm();
    }
});

const addFormSchema = toTypedSchema(
    z.object({
        addFloorName: z.string().min(2, 'Floor name is required').max(50, 'Too long'),
    }),
);

const addForm = useForm({
    validationSchema: addFormSchema,
    initialValues: {
        addFloorName: '',
    },
});

// Add onAddSubmit function that is missing
const onAddSubmit = addForm.handleSubmit((values: any) => {
    router.post(
        route('floors.store'),
        {
            name: values.addFloorName,
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
});

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
            >
                <template #description>
                    <Form id="edit-floor-form" :validation-schema="editFormSchema" as="div">
                        <form class="flex flex-col justify-center gap-4 p-6" @submit.prevent="onEditSubmit">
                            <FormField v-slot="{ componentField }" name="floorName" :validate-on-blur="false">
                                <FormItem>
                                    <FormLabel>Floor Name</FormLabel>
                                    <FormControl>
                                        <Input type="text" placeholder="Enter floor name" v-bind="componentField" />
                                    </FormControl>
                                    <FormDescription> This is the name that will be displayed for the floor. </FormDescription>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <Button class="self-end px-6" type="submit">Update</Button>
                        </form>
                    </Form>
                </template>
            </Modal>

            <!-- Add Modal -->
            <Modal v-if="addModalOpen" :title="'Enter the floor name:'" v-model:open="addModalOpen" :buttonsVisible="false" :disableEsc="false">
                <template #description>
                    <Form id="add-floor-form" :validation-schema="addFormSchema" as="div">
                        <form class="flex flex-col justify-center gap-4 p-6" @submit.prevent="onAddSubmit">
                            <FormField v-slot="{ componentField }" name="addFloorName" :validate-on-blur="false">
                                <FormItem>
                                    <FormControl>
                                        <Input type="text" placeholder="please write a descriptive name" v-bind="componentField" />
                                    </FormControl>
                                    <FormDescription> This is the name that will be displayed for the floor. </FormDescription>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <Button class="self-end" type="submit">Add</Button>
                        </form>
                    </Form>
                </template>
            </Modal>
        </div>
    </AppLayout>
</template>
