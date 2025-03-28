<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Alert from '@/components/Shared/Alert.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router, usePage } from '@inertiajs/vue3';
import { AlertCircle } from 'lucide-vue-next';
import { computed, h, ref } from 'vue';

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
const filters = ref({
    name: params.get('filter[name]') || '',
    email: params.get('filter[email]')||'',
});
const sorting = ref([
    {
        id: params.get('sort')?.replace('-', '') || '',
        desc: params.get('sort')?.includes('-') || false,
    },
]);
const pagination = ref({
    pageIndex: props.managers.meta.current_page - 1,
    pageSize: props.managers.meta.per_page,
    dataSize: props.managers.meta.total,
});

// Table Columns
const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'name', header: 'Name' },
    { accessorKey: 'email', header: 'Email' },
    { accessorKey: 'profile.national_id', header: 'National ID' },
    {
        accessorKey: 'avatar_image',
        header: 'Avatar',
        cell: ({ row }) =>
            h('div', { class: 'flex items-center justify-center' }, [
                h('img', {
                    src: row.getValue('avatar_image'),
                    alt: 'Avatar',
                    class: 'w-12 h-12 rounded-full object-cover',
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
    const params = new URLSearchParams();

    Object.entries(filters.value).forEach(([key, value]) => {
        if (value) params.append(`filter[${key}]`, value);
    });

    if (sorting.value.length > 0) {
        const sortString = sorting.value.map((s) => (s.desc ? `-${s.id}` : s.id)).join(',');
        params.append('sort', sortString);
    }
    params.append('page', (pagination.value.pageIndex + 1).toString());
    params.append('perPage', pagination.value.pageSize.toString());

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
    isEditModalOpen.value = true;
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

//AlertDismiss
const dismissError = () => {
    page.props.errors = {};
};
</script>

<template>
    <Head title="Manage Managers" />
    <!-- Errors -->
    <Alert
        class="fixed left-1/2 top-4 z-[9999] mx-auto mt-4 w-10/12 -translate-x-1/2 bg-red-500 text-white"
        v-for="(value, index) of errors"
        :key="index"
        :show="true"
        :variant="destructive"
        :title="index"
        :message="value"
    >
        <template v-slot:icon><AlertCircle class="h-4 w-4" /></template>
        <template v-slot:dismissBtn>
            <Button class="bg-white text-black" @click="dismissError">Dismiss</Button>
        </template>
    </Alert>
    <!-- Main Content -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6">
            <ManageDataTable
                title="Managers"
                :columns="columns"
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
                    <Button variant="default" @click="isAddModalOpen = true">Add Manager</Button>
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
            <ManageModal 
            v-if="isEditModalOpen" 
            title="Edit Manager" v-model:open="isEditModalOpen" :buttonsVisible="false">
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
            <ManageModal 
            v-if="isAddModalOpen" 
            title="Add Manager" v-model:open="isAddModalOpen" :buttonsVisible="false">
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
