<script setup lang="ts">
import DataTable from '@/components/Shared/ManageDataTable.vue';
import Modal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, defineProps, h, ref } from 'vue';
import type { Room } from '@/types';
// Define missing types
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Manage Rooms', href: route('rooms.index') }];

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
const sorting = params.get('sort')? ref<SortingValue[]>([
    {
        id: params.get('sort')?.replace('-', '') || '',
        desc: params.get('sort')?.includes('-') || false,
    },
]): ref<SortingValue[]>([]);
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
                    h(Button,
                        {variant: 'destructive',class: 'mx-1',disabled: info.row.original.state == 'occupied', onClick: () => handleDelete(info.row.original),},
                        () => 'Remove',
                    ),
                ]
                : '',
    },
]);

if (props.rooms?.data?.length > 0 && props.rooms.data[0]?.manager) {
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
    if (pagination.value.pageIndex > 0)
    params.append('page', pagination.value.pageIndex + 1);


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

// Form data objects
const editForm = ref({
    editRoomTitle: '',
    editRoomDescription: '',
    editRoomFloorNumber: '',
    editRoomCapacity: '',
    editRoomPrice: '',
    editRoomState: 'available',
    editRoomImage: null,
});

const addForm = ref({
    addRoomTitle: '',
    addRoomDescription: '',
    addRoomNumber: '',
    addRoomFloorNumber: '',
    addRoomCapacity: '',
    addRoomPrice: '',
    addRoomState: 'available',
    addRoomImage: null,
});

const resetAddForm = () => {
    addForm.value = {
        addRoomTitle: '',
        addRoomDescription: '',
        addRoomNumber: '',
        addRoomFloorNumber: '',
        addRoomCapacity: '',
        addRoomPrice: '',
        addRoomState: 'available',
        addRoomImage: null,
    };
    page.props.errors = {};
};

const resetEditForm = () => {
    // Clear any validation errors
    page.props.errors = {};
};

// Update modal open handlers to clear errors
const editModalOpen = ref<boolean>(false);
const handleEdit = (room: Room) => {
    resetEditForm(); // Clear previous errors
    selectedRoom.value = room;
    editModalOpen.value = true;

    // Set the form values
    editForm.value = {
        editRoomFloorNumber: room.floor.number.toString(),
        editRoomCapacity: room.capacity.toString(),
        editRoomPrice: (room.room_price / 100).toString(), // Convert from cents to dollars
        editRoomState: room.state !== 'occupied' ? room.state : 'available',
        editRoomTitle: room.title,
        editRoomDescription: room.description,
        editRoomImage: null,
    };
};

const addModalOpen = ref<boolean>(false);
const openAddModal = () => {
    resetAddForm(); // Reset form and clear errors
    addModalOpen.value = true;
};

// Update successful submission to use the reset function
const onAddSubmit = () => {
    const formData = new FormData();
    formData.append('title', addForm.value.addRoomTitle);
    formData.append('description', addForm.value.addRoomDescription);
    formData.append('number', addForm.value.addRoomNumber);
    formData.append('floor_number', addForm.value.addRoomFloorNumber);
    formData.append('capacity', addForm.value.addRoomCapacity);
    formData.append('room_price', (Number(addForm.value.addRoomPrice) * 100).toString());
    formData.append('state', addForm.value.addRoomState);
    console.log(addForm.value.addRoomImage);
    if (addForm.value.addRoomImage) {
        formData.append('image', addForm.value.addRoomImage);
    }

    router.post(route('rooms.store'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            addModalOpen.value = false;
            toast({ title: 'Room added successfully!' });
            resetAddForm();
        },
    });
};
const onEditSubmit = () => {
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('floor_number', editForm.value.editRoomFloorNumber);
    formData.append('capacity', editForm.value.editRoomCapacity);
    formData.append('room_price', Number(editForm.value.editRoomPrice) * 100);
    formData.append('state', editForm.value.editRoomState);
    formData.append('title', editForm.value.editRoomTitle);
    formData.append('description', editForm.value.editRoomDescription);

    // Append image if a new one is selected
    if (editForm.value.editRoomImage) {
        formData.append('image', editForm.value.editRoomImage);
    }
    console.log(editForm);
    router.post(
        route('rooms.update', selectedRoom.value?.number), // Change to POST if necessary
        formData,
        {
            preserveScroll: true,
            onSuccess: () => {
                editModalOpen.value = false;
                toast({ title: 'Room updated successfully!' });
            },
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        },
    );
};

// Handle file selection
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        editForm.value.editRoomImage = file;
    }
};

// We'll also clear errors when modals are closed
const closeEditModal = () => {
    editModalOpen.value = false;
    resetEditForm();
};

const closeAddModal = () => {
    addModalOpen.value = false;
    resetAddForm();
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
                        <Button @click="openAddModal" class="px-16">Add Room</Button>
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
                @update:open="
                    (val) => {
                        if (!val) resetEditForm();
                    }
                "
            >
                <template #description>
                    <div class="flex flex-col justify-center gap-4 p-6">
                        <!-- Title -->
                        <div>
                            <label class="text-sm font-medium">Title</label>
                            <Input type="text" placeholder="Enter room title" v-model="editForm.editRoomTitle" />
                            <p v-if="errors.title" class="text-sm text-red-500">{{ errors.title }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="text-sm font-medium">Description</label>
                            <Textarea placeholder="Enter room description" v-model="editForm.editRoomDescription" />
                            <p v-if="errors.description" class="text-sm text-red-500">{{ errors.description }}</p>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="text-sm font-medium">Upload Image</label>
                            <Input type="file" @change="handleImageUpload" />
                            <p v-if="errors.image" class="text-sm text-red-500">{{ errors.image }}</p>
                        </div>

                        <!-- Floor Number -->
                        <div>
                            <label class="text-sm font-medium">Floor Number</label>
                            <Input type="text" placeholder="4 digits floor number" v-model="editForm.editRoomFloorNumber" />
                            <p v-if="errors.floor_number" class="text-sm text-red-500">{{ errors.floor_number }}</p>
                        </div>

                        <!-- Room Capacity -->
                        <div>
                            <label class="text-sm font-medium">Room Capacity</label>
                            <Input type="text" placeholder="Capacity: Maximum is 5" v-model="editForm.editRoomCapacity" />
                            <p v-if="errors.capacity" class="text-sm text-red-500">{{ errors.capacity }}</p>
                        </div>

                        <!-- Room Price -->
                        <div>
                            <label class="text-sm font-medium">Room Price</label>
                            <Input type="text" placeholder="price in $" v-model="editForm.editRoomPrice" />
                            <p v-if="errors.room_price" class="text-sm text-red-500">{{ errors.room_price }}</p>
                        </div>

                        <!-- Room State -->
                        <div>
                            <label class="text-sm font-medium">Room State</label>
                            <Select v-model="editForm.editRoomState">
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
                            <p v-if="errors.state" class="text-sm text-red-500">{{ errors.state }}</p>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button variant="outline" @click="closeEditModal">Cancel</Button>
                            <Button @click="() => onEditSubmit()">Update</Button>
                        </div>
                    </div>
                </template>
            </Modal>

            <!-- Add Modal -->
            <Modal
                v-if="addModalOpen"
                title="Enter the new room data:"
                v-model:open="addModalOpen"
                :buttonsVisible="false"
                :disableEsc="false"
                @update:open="(val) => {if (!val) resetAddForm();}">
                <template #description>
                    <div class="flex flex-col justify-center gap-4 p-6">
                        <div>
                            <label class="text-sm font-medium">Title</label>
                            <Input type="text" placeholder="Enter room title" v-model="addForm.addRoomTitle" />
                            <p v-if="errors.title" class="text-sm text-red-500">{{ errors.title }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Description</label>
                            <Textarea placeholder="Enter room description" v-model="addForm.addRoomDescription" />
                            <p v-if="errors.description" class="text-sm text-red-500">{{ errors.description }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Room Number</label>
                            <Input type="text" placeholder="4 digits room number" v-model="addForm.addRoomNumber" />
                            <p v-if="errors.number" class="text-sm text-red-500">{{ errors.number }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Floor Number</label>
                            <Input type="text" placeholder="4 digits floor number" v-model="addForm.addRoomFloorNumber" />
                            <p v-if="errors.floor_number" class="text-sm text-red-500">{{ errors.floor_number }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Room Capacity</label>
                            <Input type="text" placeholder="Capacity: Maximum is 5" v-model="addForm.addRoomCapacity" />
                            <p v-if="errors.capacity" class="text-sm text-red-500">{{ errors.capacity }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Room Price</label>
                            <Input type="text" placeholder="price in $" v-model="addForm.addRoomPrice" />
                            <p v-if="errors.room_price" class="text-sm text-red-500">{{ errors.room_price }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Room State</label>
                            <Select v-model="addForm.addRoomState">
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
                            <p v-if="errors.state" class="text-sm text-red-500">{{ errors.state }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Room Image</label>
                            <Input type="file"  @change="(e) => addForm.addRoomImage = e.target.files[0]" />
                            <p v-if="errors.image" class="text-sm text-red-500">{{ errors.image }}</p>
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
