<script setup lang="ts">
import Alert from '@/components/Shared/Alert.vue';
import DataTable from '@/components/Shared/ManageDataTable.vue';
import Modal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { toTypedSchema } from '@vee-validate/zod';
import { AlertCircle } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { computed, defineProps, h, ref } from 'vue';
import * as z from 'zod';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Manage Rooms', href: route('rooms.index') },
];

const page = usePage();
const props = defineProps(['rooms']);
const errors = computed(() => page.props.errors);
const params = new URLSearchParams(window.location.search);
const filters = ref({
    number: params.get('filter[number]') || '',
    capacity: params.get('filter[capacity]'),
    room_price: params.get('filter[room_price]'),
    state: params.get('filter[state]'),
    floor_number: params.get('filter[floor_number]'),
});
const sorting = ref<SortingValue[]>([
    {
        id: params.get('sort')?.replace('-', '') || '',
        desc: params.get('sort')?.includes('-') || false,
    },
]);
const pagination = ref({
    pageIndex: props.rooms.meta.current_page - 1,
    pageSize: props.rooms.meta.per_page,
    dataSize: props.rooms.meta.total,
});

const columns = ref<ColumnDef<Room>[]>([
    { accessorKey: 'number', header: 'Room Number' },
    { accessorKey: 'capacity', header: 'Room Capacity' },
    { accessorKey: 'room_price', header: 'Price in $' },
    { accessorKey: 'state', header: 'State' },
    { accessorKey: 'floor.number', header: 'Floor Number' },
    {
        accessorKey: 'Edit',
        header: 'Actions',
        cell: (info: any) =>
            info.row.original.manager_id == page.props.auth.user.id || page.props.auth.user.roles.includes('admin')
                ? [
                    h(Button, { variant: 'default', class: 'mx-1', onClick: () => handleEdit(info.row.original) }, () => 'Edit'),
                    h(Button, {
                        variant: 'destructive',
                        class: 'mx-1',
                        disabled: info.row.original.state == 'occupied',
                        onClick: () => handleDelete(info.row.original),
                    }, () => 'Remove'),
                ]
                : '',
    },
]);

if (props.rooms?.data?.length > 0 && props.rooms.data[0]?.manager) {
    columns.value.splice(2, 0, { accessorKey: 'manager.name', header: 'Manager' });
}

const fetchData = (url?: string) => {
    const params = new URLSearchParams();
    if (filters.value.room_price) {
        params.append('filter[room_price]', (filters.value.room_price * 100).toString());
    }
    Object.entries(filters.value).forEach(([key, value]) => {
        if (value && key !== 'room_price') params.append(`filter[${key}]`, value);
    });
    if (sorting.value.length > 0) {
        const sortString = sorting.value.map((s: SortingValue) => (s.desc ? `-${s.id}` : s.id)).join(',');
        params.append('sort', sortString);
    }
    params.append('page', (pagination.value.pageIndex + 1).toString());
    params.append('perPage', pagination.value.pageSize.toString());
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

const editModalOpen = ref<boolean>(false);
const handleEdit = (room: Room) => {
    selectedRoom.value = room;
    editModalOpen.value = true;
};

const editFormSchema = toTypedSchema(z.object({
    editRoomFloorNumber: z.string().min(4).max(50).regex(/^[0-9]+$/),
    editRoomCapacity: z.string().refine(value => !isNaN(Number(value)) && Number(value) > 0 && Number(value) <= 5),
    editRoomPrice: z.string().refine(value => !isNaN(Number(value)) && Number(value) >= 10),
    editRoomState: z.enum(['available', 'maintenance']),
}));

const editForm = useForm({ validationSchema: editFormSchema, initialValues: {} });
const onEditSubmit = editForm.handleSubmit(editValues => {
    router.put(route('rooms.update', selectedRoom.value?.number), editValues, {
        preserveScroll: true,
        onSuccess: () => {
            editModalOpen.value = false;
            toast({ title: 'Room updated successfully!' });
        },
    });
});

const addModalOpen = ref<boolean>(false);
const addFormSchema = toTypedSchema(
    z.object({
        addRoomNumber: z
            .string()
            .min(4, 'Room Number should exceed 4 digits')
            .max(50, 'Too long')
            .regex(/^[0-9]+$/, 'Must be a number'),
        addRoomFloorNumber: z
            .string()
            .min(4, 'Floor number should exceed 4 digits')
            .max(50, 'Too long')
            .regex(/^[0-9]+$/, 'Must be a number'),
        addRoomCapacity: z
            .string()
            .refine((value: string) => !isNaN(Number(value)) && Number(value) <= 5 && Number(value) > 0, 'Min:1 - Max:5'),
        addRoomPrice: z
            .string()
            .refine((value: string) => !isNaN(Number(value)) && Number(value) >= 10, 'Minimum is 10$'),
        addRoomState: z.enum(['available', 'maintenance']),
    })
);

const addForm = useForm({
    validationSchema: addFormSchema,
    initialValues: {
        addRoomNumber: '',
        addRoomFloorNumber: '',
        addRoomCapacity: '',
        addRoomPrice: '',
        addRoomState: '',
    },
});

const onAddSubmit = addForm.handleSubmit((addValues: any) => {
    console.log(addValues);
    router.post(
        route('rooms.store'),
        {
            number: addValues.addRoomNumber,
            floor_number: addValues.addRoomFloorNumber,
            capacity: addValues.addRoomCapacity,
            room_price: Number(addValues.addRoomPrice) * 100,
            state: addValues.addRoomState,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                addModalOpen.value = false;
                toast({ title: 'Room added successfully!' });
                router.get(page.url);
            },
        }
    );
});


const dismissError = () => {
    page.props.errors = {};
};
</script>

<template>
    <Head title="Manage Rooms" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl px-4 py-4">
            <!-- Errors -->
            <Alert
                class="fixed left-1/2 top-4 z-[9999] mx-auto mt-4 w-10/12 -translate-x-1/2 bg-red-500 text-white"
                v-for="(value, index) of errors"
                :key="index"
                :show="true"
                :variant="'destructive'"
                :title="index"
                :message="value"
            >
                <template v-slot:icon><AlertCircle class="h-4 w-4" /></template>
                <template v-slot:dismissBtn>
                    <Button class="bg-white text-black" @click="dismissError">Dismiss</Button>
                </template>
            </Alert>

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
                    @update:sorting="(newSorting) => { sorting = newSorting; fetchData(); }"
                    @update:filters="(newFilters) => { filters = newFilters; fetchData(); }"
                    @update:pagination="(newPagination) => { pagination = newPagination; fetchData(); }"
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
            >
                <template #description>
                    <Form id="edit-floor-form" :validation-schema="editFormSchema" @submit.prevent="onEditSubmit" as="div">
                        <form class="flex flex-col justify-center gap-4 p-6" @submit.prevent="onEditSubmit">
                            <FormField v-slot="{ componentField }" name="editRoomFloorNumber">
                                <FormItem>
                                    <FormControl>
                                        <Input type="text" placeholder="4 digits floor number" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ componentField }" name="editRoomCapacity">
                                <FormItem>
                                    <FormControl>
                                        <Input type="text" placeholder="Capacity: Maximum is 5" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ componentField }" name="editRoomPrice">
                                <FormItem>
                                    <FormControl>
                                        <Input type="text" placeholder="price in $" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ componentField }" name="editRoomState">
                                <FormItem>
                                    <FormControl>
                                        <Select v-bind="componentField">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select current room state" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem value="available">available</SelectItem>
                                                    <SelectItem value="maintenance">maintenance</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <Button type="submit" class="self-end">Update</Button>
                        </form>
                    </Form>
                </template>
            </Modal>

            <!-- Add Modal -->
            <Modal v-if="addModalOpen" title="Enter the new room data:" v-model:open="addModalOpen" :buttonsVisible="false" :disableEsc="false">
                <template #description>
                    <Form id="add-floor-form" :validation-schema="addFormSchema" as="div">
                        <form class="flex flex-col justify-center gap-4 p-6" >
                            <FormField v-slot="{ componentField }" name="addRoomNumber">
                                <FormItem>
                                    <FormControl>
                                        <Input type="text" placeholder="4 digits room number" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ componentField }" name="addRoomFloorNumber">
                                <FormItem>
                                    <FormControl>
                                        <Input type="text" placeholder="4 digits floor number" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ componentField }" name="addRoomCapacity">
                                <FormItem>
                                    <FormControl>
                                        <Input type="text" placeholder="Capacity: Maximum is 5" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ componentField }" name="addRoomPrice">
                                <FormItem>
                                    <FormControl>
                                        <Input type="text" placeholder="price in $" v-bind="componentField" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ componentField }" name="addRoomState">
                                <FormItem>
                                    <FormControl>
                                        <Select v-bind="componentField">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select current room state" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem value="available">available</SelectItem>
                                                    <SelectItem value="maintenance">maintenance</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </FormControl>
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
