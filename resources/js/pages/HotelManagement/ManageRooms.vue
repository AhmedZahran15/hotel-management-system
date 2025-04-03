<script setup lang="ts">
import DataTable from '@/components/Shared/ManageDataTable.vue';
import Modal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { toast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, defineProps, h, ref } from 'vue';
import type { Room } from '@/types';
import {formulateURL, extractSorting} from '@/utils/helpers';
import * as z from 'zod'
import { Input } from '@/components/ui/input';
import Form from '@/components/Shared/Form.vue';

// Define missing types
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Manage Rooms', href: route('rooms.index') }];

const page = usePage();
const props = defineProps(['rooms']);
const errors = computed(() => page.props.errors);
const params = new URLSearchParams(window.location.search);
const filters = ref([
    {column:"Room Number", value: params.get('filter[number]')||'', urlName: 'number'},
    {column:"Room Capacity", value: params.get('filter[capacity]')||'', urlName: 'capacity'},
    {column:"Room Price", value: params.get('filter[room_price]')||'', urlName: 'room_price'},
    {column:"State", value: params.get('filter[state]')||'', urlName: 'state'},
    {column:"Floor Number", value: params.get('filter[floor_number]')||'', urlName: 'floor_number'},
]);

const sorting = ref(extractSorting(params));

const pagination = ref({
    pageIndex: props.rooms.meta.current_page - 1,
    pageSize: props.rooms.meta.per_page,
    dataSize: props.rooms.meta.total,
});

const columns = ref<ColumnDef<Room>[]>([
    { accessorKey: 'number', header: 'Room Number', sortable: true },
    { accessorKey: 'capacity', header: 'Room Capacity', sortable: true },
    { accessorKey: 'room_price', header: 'Price in $' ,sortable: true },
    { accessorKey: 'state', header: 'State', sortable: true },
    { accessorKey: 'floor_number', header: 'Floor Number', sortable: true },
    {
        accessorKey: 'Edit',
        header: 'Actions',
        cell: (info: any) =>
            info.row.original.manager_id == page.props.auth.user.id || page.props.auth.user.roles.includes('admin')
                ? [
                    h(Button, { variant: 'default', class: 'mx-1', onClick: () => handleEdit(info.row.original) }, () => 'Edit'),
                    h(Button,
                        {variant: 'destructive',class: 'mx-1',disabled: info.row.original.state == 'occupied', onClick: () => handleDelete(info.row.original),},
                        () => 'Remove',
                    ),
                ]
                : '',
    },
]);

if (props.rooms?.data?.length > 0 && props.rooms.data[0]?.manager) {
    columns.value.splice(2, 0, { accessorKey: 'manager.name', header: 'Manager', sortable: true });
}

const fetchData = (url?: string) => {

    const params =formulateURL(filters.value, sorting.value, pagination.value);
    router.get(url || route('rooms.index'), Object.fromEntries(params.entries()), {
        preserveScroll: true,
        preserveState: true,
        only: ['rooms'],
        onSuccess: () => {
            pagination.value = {
                pageIndex: props.rooms.meta.current_page - 1,
                pageSize: props.rooms.meta.per_page,
                dataSize: props.rooms.meta.total,
            };
        },
    });
};

const selectedRoom = ref<Room | null>(null);

// Delete Modal
const deleteModalOpen = ref<boolean>(false);
const handleDelete = (room: Room) => {
    selectedRoom.value = room;
    deleteModalOpen.value = true;
};
const deleteConfirmed = () => {
    if (selectedRoom.value?.number) {
        router.delete(route('rooms.destroy', selectedRoom.value.number));
        deleteModalOpen.value = false;
    }
    selectedRoom.value = null;
};

// Add Form Schema
const FormSchema =
    z.object({
        title: z.string().min(3, 'Room title is required').max(50, 'Too long').describe('Room title'),
        description: z.string().min(3, 'Room description is required').max(250, 'Too long').describe('Room description'),
        number: z.number().min(1000, 'Room Number is required').max(99999, 'Too long').describe('Room Number'),
        floor_number: z.number().min(1000, 'Floor Number is required').max(99999, 'Too long').describe('Floor Number'),
        capacity: z.number().min(1, 'Capacity is required').max(5, 'Max capacity is 5').describe('Room Capacity'),
        room_price: z.number().min(10, 'Price cannot be less than $10').max(1000000, 'Max price is $1000000').describe('Room Price'),
        state: z.enum(['available', 'maintenance']).describe('Room State'),
        image: z.instanceof(File).describe('Room Image'),

    });
const fieldConfig = {
    image: {
        inputProps: { type: 'file', accept: 'image/jpeg, image/jpg' },
        component: 'file',
    },
}
// Add
const addModalOpen = ref<boolean>(false);
const onAddSubmit = (data: any) => {
    const formData = {...data};
    formData.room_price*=100 ;
    router.post(route('rooms.store'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            addModalOpen.value = false;
            toast({ title:'Room added successfully!' });
        },
    });
};

// Edit Form Schema
const editFormSchema =
    z.object({
        title: z.string().min(3, 'Room title is required').max(50, 'Too long').describe('Room title'),
        description: z.string().min(3, 'Room description is required').max(250, 'Too long').describe('Room description'),
        floor_number: z.number().min(1000, 'Floor Number is required').max(99999, 'Too long').describe('Floor Number'),
        capacity: z.number().min(1, 'Capacity is required').max(5, 'Max capacity is 5').describe('Room Capacity'),
        room_price: z.number().min(10, 'Price cannot be less than $10').max(1000000, 'Max price is $1000000').describe('Room Price'),
        state: z.enum(['available', 'maintenance']).describe('Room State'),
        image: z.instanceof(File).optional().describe('Room Image'),
    });
//Editing
const editModalOpen = ref<boolean>(false);

const handleEdit = (room: Room) => {
    selectedRoom.value = room;
    selectedRoom.value.floor_number = room.floor.number;
    editModalOpen.value = true;
};


const onEditSubmit = (data:any) => {
    const formData = {...data, image: image.value};
    formData.room_price*=100 ;
    formData.number = selectedRoom.value.number;
    if(image.value) formData.image = image.value;
    else delete formData.image;
    formData["_method"] ="PUT";
    router.post(
        route('rooms.update', selectedRoom.value?.number),
        formData,
        {
            preserveScroll: true,
            onSuccess: () => {
                editModalOpen.value = false;
                toast({ title: 'Room updated successfully!' });
            },
        },
    );
};

</script>
<template>
    <Head title="Manage Rooms" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl px-4 py-4">
            <!-- DataTable -->
            <div class="px-6">
                <DataTable
                    :columns="columns"
                    :data="props.rooms.data"
                    :filters="filters"
                    :pagination="pagination"
                    :meta="props.rooms.meta"
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
                        <Button @click="addModalOpen = true" class="px-16">Add Room</Button>
                    </template>
                </DataTable>
            </div>

            <!-- Delete Modal -->
            <Modal
                v-if="deleteModalOpen"
                :title="'Deleting Room no. ' + selectedRoom?.number.toString()"
                v-model:open="deleteModalOpen"
                @confirm="deleteConfirmed"

            >
                <template #description>
                    <div class="flex flex-col gap-2 py-3">
                        <p class="text-lg">Are you sure you want to delete this Room?</p>
                        <p class="text-xs">Note: Can't delete room containing rooms</p>
                    </div>
                </template>
            </Modal>

            <!-- Edit Modal -->
            <Modal
                v-if="editModalOpen"
                :title="'Updating Room No. ' + selectedRoom?.number.toString()"
                v-model:open="editModalOpen"
                :buttonsVisible="false"
                :disableEsc="false"
                :errors="errors"
                >
                <template #description>
                    <Form :schema="editFormSchema"
                            :fieldConfig="fieldConfig"
                            :submitText="'Update'"
                            :initialValues="selectedRoom"
                            @submit="onEditSubmit($event);"
                            @cancel="editModalOpen = false">
                            </Form>

                </template>
            </Modal>

            <!-- Add Modal -->
            <Modal
                v-if="addModalOpen"
                title="Enter the new room data:"
                v-model:open="addModalOpen"
                :buttonsVisible="false"
                :disableEsc="false"
                :errors="errors"
                @update:open="(val) => {if (!val) resetAddForm();}">
                <template #description>
                    <Form :schema="FormSchema"
                            :fieldConfig="fieldConfig"
                            :submitText="'Add'"
                            :initialValues="{}"
                            @submit="onAddSubmit($event);"
                            @cancel="addModalOpen = false">
                            </Form>
                </template>
            </Modal>

        </div>
    </AppLayout>
</template>
