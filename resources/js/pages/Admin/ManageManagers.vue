<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import {formulateURL, extractSorting} from '@/utils/helpers';


// Breadcrumbs for navigation
const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Managers', href: route('managers.index') },
];

const props = defineProps(['managers']);
const page = usePage();

// State Variables
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedManagerId = ref(null);
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    national_id: '',
    avatar_image: null });

const errors = computed(() => page.props.errors);
const params = new URLSearchParams(window.location.search);

const filters = ref([
    {column:"Name", value: params.get('filter[name]')||'', urlName: 'name'},
    {column:"Email", value: params.get('filter[email]')||'', urlName: 'email'},
]);

const sorting = ref(extractSorting(params));

const pagination = ref({
    pageIndex: props.managers.meta.current_page - 1,
    pageSize: props.managers.meta.per_page,
    dataSize: props.managers.meta.total,
});


// Table Columns
const columns = [
    { accessorKey: 'id', header: 'ID', sortable:true },
    { accessorKey: 'name', header: 'Name', sortable:true },
    { accessorKey: 'email', header: 'Email', sortable:true },
    { accessorKey: 'profile.national_id', header: 'National ID' },
    {
        accessorKey: 'avatar_image',
        header: 'Avatar',
        cell: ({ row }) =>
            h('div', { class: 'flex items-center justify-center' }, [
                h('img', {
                    src: row.getValue('avatar_image'),
                    alt: 'Avatar',
                    class: 'w-12 h-12 rounded-full object-cover dark:bg-gray-600 ',
                }),
            ]),
    },
    {
        accessorKey: 'actions',
        header: 'Actions',
        cell: ({ row }) => [
            h(Button, { variant: 'default', class: 'mx-1', onClick: () => openEditModal(row.original) }, () => 'Edit'),
            h(Button, { variant: 'destructive', class: 'mx-1', onClick: () => openDeleteModal(row.original.id) }, () => 'Remove'),
        ],
    },
];

// Fetch Managers
const fetchManagers = () => {
    console.log(sorting.value)
    const params = formulateURL(filters.value, sorting.value,pagination.value);

    router.get(route('managers.index'), Object.fromEntries(params.entries()), {
        preserveScroll: true,
        preserveState: true,
        only: ['managers'],
        onSuccess: () => {
            pagination.value = {
                pageIndex: props.managers.meta.current_page - 1,
                pageSize: props.managers.meta.per_page,
                dataSize: props.managers.meta.total,
            };
        },
    });
};

// Open Edit Modal
const openEditModal = (manager) => {
    form.value = { ...manager, avatar_image: null };
        page.props.errors = {};
    isEditModalOpen.value = true;
};
// Open Add Modal
const openAddModal = () => {
    form.value = { name: '', email: '', password: '', password_confirmation: '', national_id: '', avatar_image: null };
    page.props.errors = {};
    isAddModalOpen.value = true;
};

// Open Delete Modal
const openDeleteModal = (id) => {
    selectedManagerId.value = id;
    isDeleteModalOpen.value = true;
};

// Handle Image Upload
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    delete errors.value.avatar_image;
    if (file && !['image/jpeg', 'image/jpg'].includes(file.type)) {
        errors.value.avatar_image = 'Only JPG and JPEG files are allowed.';
        form.value.avatar_image = null;
        return;
    }
    form.value.avatar_image = file;
};

// Handle Add Manager
const handleAdd = () => {
    const formData = new FormData();
    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null) formData.append(key, form.value[key]);
    });
    router.post(route('managers.store'), formData, {
        onSuccess: () => {
            isAddModalOpen.value = false;
        },
    });
};

// Handle Edit Manager
const handleEdit = () => {
    const formData = new FormData();
    formData.append('_method', 'PATCH');
    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null) formData.append(key, form.value[key]);
    });

    router.post(`/dashboard/managers/${form.value.id}`, formData, {
        onSuccess: () => {
            isEditModalOpen.value = false;
        },
    });
};

// Confirm Delete
const confirmDelete = () => {
    router.delete(`/dashboard/managers/${selectedManagerId.value}`, {
        preserveState: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        },
    });
    isDeleteModalOpen.value = false;
};

</script>

<template>
    <Head title="Manage Managers" />
    <!-- Main Content -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6">
            <ManageDataTable
                title="Managers"
                :columns="columns"
                :errors="errors"
                :data="props.managers.data"
                :pagination="pagination"
                :filters="filters"
                :manual-pagination="true"
                :manual-sorting="true"
                :manual-filtering="true"
                :sorting="sorting"
                @update:sorting="
                    (newSorting) => {
                        sorting = newSorting;
                        fetchManagers();
                    }
                "
                @update:filters="
                    (newFilters) => {
                        filters = newFilters;
                        fetchManagers();
                    }
                "
                @update:pagination="
                    (newPagination) => {
                        pagination = newPagination;
                        fetchManagers();
                    }
                "
            >
                <template #table-action>
                    <Button variant="default" @click="openAddModal">Add Manager</Button>
                </template>
            </ManageDataTable>

            <!-- Delete Modal -->
            <ManageModal
            v-if="isDeleteModalOpen"
            title="Deleting Manager" v-model:open="isDeleteModalOpen" :buttonsVisible="false">
                <template #description>
                    <p class="text-lg">Are you sure you want to delete this manager?</p>
                </template>
                <template #footer>
                    <Button variant="secondary" @click="isDeleteModalOpen = false">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete</Button>
                </template>
            </ManageModal>

            <!-- Edit Modal -->
            <ManageModal v-if="isEditModalOpen" title="Edit Manager" v-model:open="isEditModalOpen" :buttonsVisible="false" :errors="errors">
                <template #description>
                    <form class="flex flex-col gap-4 p-6" @submit.prevent="handleEdit">
                        <div class="flex flex-col gap-1">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" />
                        </div>

                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" />

                        <Label for="national_id">National ID</Label>
                        <Input id="national_id" v-model="form.national_id" />

                        <Label for="avatar">Avatar</Label>
                        <Input id="avatar" type="file" @change="handleImageUpload" />

                        <div class="flex flex-col gap-1">
                            <Label for="password">Password (leave empty to keep current)</Label>
                            <Input id="password" type="password" v-model="form.password" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button variant="secondary" @click="isEditModalOpen = false">Close</Button>
                            <Button type="submit">Update</Button>
                        </div>
                    </form>
                </template>
            </ManageModal>

            <!-- Add Modal -->
            <ManageModal v-if="isAddModalOpen" title="Add Manager"
            v-model:open="isAddModalOpen" :disableEsc="false" :buttonsVisible="false" :errors="errors">
                <template #description>
                    <form class="flex flex-col gap-4 p-6" @submit.prevent="handleAdd">
                        <div class="flex flex-col gap-1">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="national_id">National ID</Label>
                            <Input id="national_id" v-model="form.national_id" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="avatar">Avatar</Label>
                            <Input id="avatar" type="file" @change="handleImageUpload" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="password">Password</Label>
                            <Input id="password" v-model="form.password" type="password" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input id="password_confirmation" v-model="form.password_confirmation" type="password" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button variant="secondary" @click="isAddModalOpen = false">Close</Button>
                            <Button type="submit">Add</Button>
                        </div>
                    </form>
                </template>
            </ManageModal>
        </div>
    </AppLayout>
</template>
