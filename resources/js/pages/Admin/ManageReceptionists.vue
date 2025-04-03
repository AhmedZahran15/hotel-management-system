<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import Form from '@/components/Shared/Form.vue';
import { Button } from '@/components/ui/button';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import {formulateURL, extractSorting} from '@/utils/helpers';
import * as z from 'zod';

//error ,success messages
const successMessage = ref('');
const errorMessage = ref('');

//Specify user id and role
const loggedInUserId = ref(null);
const loggedInUserRole = ref('');

// Breadcrumbs for navigation
const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Receptionists', href: route('receptionists.index'), active: true },
];

const props = defineProps(['receptionists']);
const page = usePage();

// Errors
const errors = computed(() => page.props.errors);

// User and Admin Check
const user = page.props.auth.user;
const isAdmin = user?.roles[0].includes('admin');

// State Variables
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedReceptionist = ref(null);
const isLoading = ref(false);
const params = new URLSearchParams(window.location.search);

const filters = ref([
    {column:"Name", value: params.get('filter[name]')||'', urlName: 'name'},
    {column:"Email", value: params.get('filter[email]')||'', urlName: 'email'},
]);

const sorting = ref(extractSorting(params));

const pagination = ref({
    pageIndex: props.receptionists.meta.current_page - 1,
    pageSize: props.receptionists.meta.per_page,
    dataSize: props.receptionists.meta.total,
});

// Table Columns
const columns = [
    { accessorKey: 'id', header: 'ID', sortable: true },
    { accessorKey: 'name', header: 'Name', sortable: true },
    { accessorKey: 'email', header: 'Email', sortable: true },
    {
        accessorKey: 'created_at',
        header: 'Created At',
        sortable: true,
        cell: (info) => new Date(info.getValue()).toLocaleString(),
    },
    ...(isAdmin
        ? [
            {
                accessorKey: 'creator',
                header: 'Manager',
                cell: (info) => {
                    const creator = info.getValue();
                    return creator && creator.name ? creator.name : 'Manager not found';
                },
            },
        ]
        : []),
        {
            accessorKey: 'Actions',
            header: 'Actions',
            cell: (info) => {
                const isAdmin = user?.roles[0].includes('admin');
                const isManagerAndCreator = user?.roles[0].includes('manager') && (user.id === info.row.original
                .creator_user_id);
                return [
                    h(
                        Button,
                        {
                        variant: 'default',
                        class: 'mx-1',
                        onClick: () => openEditModal(info.row.original),
                        disabled: !(isAdmin || isManagerAndCreator),
                    },
                    () => 'Edit',
                ),
                h(
                    Button,
                    {
                        variant: 'destructive',
                        class: 'mx-1',
                        onClick: () => openDeleteModal(info.row.original),
                        disabled: !(isAdmin || isManagerAndCreator),
                    },
                    () => 'Remove',
                ),
                info.row.original.banned_at
                    ? h(
                        Button,
                        {
                            variant: 'default',
                            class: 'mx-1 bg-yellow-500 hover:bg-yellow-600 text-white',
                            onClick: () => handleUnban(info.row.original.id),
                            disabled: !(isAdmin || isManagerAndCreator) || isLoading.value,
                        },
                        () => 'Unban',
                    )
                    : h(
                        Button,
                        {
                            variant: 'default',
                            class: 'mx-1 bg-yellow-500 hover:bg-yellow-600 text-white',
                            onClick: () => handleBan(info.row.original.id),
                            disabled: !(isAdmin || isManagerAndCreator) || isLoading.value,
                        },
                        () => 'Ban',
                    ),
            ];
        },
    },
];

// Fetch Receptionists
const fetchReceptionists = () => {
    const params = formulateURL(filters.value, sorting.value, pagination.value);
    router.get(route('receptionists.index'), Object.fromEntries(params.entries()), {
        preserveScroll: true,
        preserveState: true,
        only: ['receptionists'],
        onSuccess: () => {
            pagination.value = {
                pageIndex: props.receptionists.meta.current_page - 1,
                pageSize: props.receptionists.meta.per_page,
                dataSize: props.receptionists.meta.total,
            };
            loggedInUserId.value = page.props.auth.user.id;
            loggedInUserRole.value = page.props.auth.user.roles[0];
        },
    });
};

// Open Add Modal
const openAddModal = () => {
    page.props.errors = {};
    isAddModalOpen.value = true;
};
const formSchema =
z.object({
    name: z.string().min(3, 'Receptionist name is required').max(50, 'Too long').describe('Receptionist name'),
    email: z.string().email('Receptionist email is required').max(100, 'Too long').describe('Receptionist email'),
    national_id: z.string().regex(new RegExp(/^[0-9]{10,}$/)).describe('National ID'),
    password: z.string().min(8, 'Password must be at least 8 characters long').describe('Password'),
    password_confirmation: z.string().describe('Password Confirmation'),
    avatar_image: z.instanceof(File).optional().describe('Avatar'),
    })
    .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
});

const fieldConfig = {
    password_confirmation: {
        inputProps: { type: 'password' },
    },
    password: {
        inputProps: { type: 'password' },
    },
    avatar_image: {
        inputProps: { type: 'file', accept: 'image/jpeg, image/jpg' },
        component: 'file',
    },
};

const onAddSubmit = (data: any) => {
const formData = { ...data };
  // add any additional form data processing here
  router.post(route('receptionists.store'), formData, {
    onSuccess: () => {
      isAddModalOpen.value = false;
    },
  });
};




// Open Edit Modal
const openEditModal = (receptionist:any) => {
    selectedReceptionist.value = { ...receptionist };
    selectedReceptionist.value.national_id = receptionist.profile.national_id;
    delete selectedReceptionist.value.avatar_image;
    page.props.errors = {};
    isEditModalOpen.value = true;
};

const editFormSchema = z
    .object({
        name: z.string().min(3, 'Receptionist name is required').max(50, 'Too long').describe('Receptionist name'),
        email: z.string().email('Receptionist email is required').max(100, 'Too long').describe('Receptionist email'),
        national_id: z.string().regex(new RegExp(/^[0-9]{10,}$/)).describe('National ID'),
        password: z.string().min(8, 'Password must be at least 8 characters long').optional().describe('Password'),
        password_confirmation: z.string().optional().describe('Password Confirmation'),
        avatar_image: z.instanceof(File).optional().describe('Avatar'),
        })
   .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
});
const onEditSubmit = (data: any) => {
    const formData = { ...data };
    formData["_method"] = 'PUT';
    router.post(route('receptionists.update', selectedReceptionist.value?.id), formData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
        },
    });
};


// Open Delete Modal
const openDeleteModal = (receptionist:any) => {
    selectedReceptionist.value = receptionist;
    isDeleteModalOpen.value = true;
};

// Confirm Delete
const confirmDelete = () => {
    if(!selectedReceptionist.value) return;
    router.delete(route('receptionists.destroy', selectedReceptionist.value.id), {
        preserveState: true,
    });
    isDeleteModalOpen.value = false;
};

// Handle Ban
const handleBan = (id) => {
        isLoading.value = true;
        router.post(route('ban', id),{},
            {
                onSuccess: () => {
                    isLoading.value = false;
                    successMessage.value = 'Receptionist has been banned successfully.';
                    errorMessage.value = '';
                    setTimeout(() => {
                        successMessage.value = '';
                    }, 3000);
                },
            },
        );
};

// Handle Unban
const handleUnban = (id) => {
        router.post(`/dashboard/receptionists/${id}/unban`);
        successMessage.value = 'Receptionist has been unbanned successfully.';
        errorMessage.value = '';
        setTimeout(() => {
            successMessage.value = '';
        }, 5000);
};
</script>

<template>
    <Head title="Manage Receptionists" />
    <!-- Main Content -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6">
            <div v-if="successMessage" class="rounded bg-green-500 p-4 text-white">
                {{ successMessage }}
            </div>

            <div v-if="errorMessage" class="rounded bg-red-500 p-4 text-white">
                {{ errorMessage }}
            </div>

            <ManageDataTable
                title="Receptionists"
                :columns="columns"
                :data="props.receptionists.data"
                :pagination="pagination"
                :filters="filters"
                :manual-pagination="true"
                :manual-sorting="true"
                :manual-filtering="true"
                :sorting="sorting"
                @update:sorting="
                    (newSorting) => {
                        sorting = newSorting;
                        fetchReceptionists();
                    }
                "
                @update:filters="
                    (newFilters) => {
                        filters = newFilters;
                        fetchReceptionists();
                    }
                "
                @update:pagination="
                    (newPagination) => {
                        pagination = newPagination;
                        fetchReceptionists();
                    }
                "
            >
                <template #table-action>
                    <Button variant="default" @click="openAddModal">Add Receptionist</Button>
                </template>
            </ManageDataTable>
            <!-- Add Modal -->
            <ManageModal v-if="isAddModalOpen" title="Add Receptionist" v-model:open="isAddModalOpen" :buttonsVisible="false" :errors="errors">
                <template #description>
                    <Form
                        :schema="formSchema"
                        :submitText="'Add Receptionist'"
                        :initialValues="{}"
                        :fieldConfig="fieldConfig"
                        @submit="onAddSubmit($event)"
                        @cancel="isAddModalOpen = false "
                    >
                    </Form>
                </template>
            </ManageModal>

            <!-- Edit Modal -->
            <ManageModal v-if="isEditModalOpen" title="Edit Receptionist" v-model:open="isEditModalOpen" :buttonsVisible="false" :errors="errors">
                <template #description>
                    <Form
                        :schema="editFormSchema"
                        :submitText="'Update Receptionist'"
                        :initialValues="selectedReceptionist"
                        :fieldConfig="fieldConfig"
                        @submit="onEditSubmit($event)"
                        @cancel="isEditModalOpen = false "
                    >
                    </Form>
                </template>
            </ManageModal>
            <!-- Delete Modal -->
            <ManageModal v-if="isDeleteModalOpen" title="Deleting Receptionist" v-model:open="isDeleteModalOpen" :buttonsVisible="false">
                <template #description>
                    <p class="text-lg">Are you sure you want to delete this Receptionist?</p>
                </template>

                <template #footer>
                    <Button variant="secondary" @click="isDeleteModalOpen = false">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete</Button>
                </template>
            </ManageModal>
        </div>
    </AppLayout>
</template>
