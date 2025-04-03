<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Form from '@/components/Shared/Form.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import {formulateURL, extractSorting} from '@/utils/helpers';
import * as z from 'zod';


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
const selectedManager = ref(null);
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
            h(Button, { variant: 'destructive', class: 'mx-1', onClick: () => openDeleteModal(row.original) }, () => 'Remove'),
        ],
    },
];

// Fetch Managers
const fetchManagers = () => {
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

const formSchema =
z.object({
    name: z.string().min(3, 'Manager name is required').max(50, 'Too long').describe('Manager name'),
    email: z.string().email('Manager email is required').max(100, 'Too long').describe('Manager email'),
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
  router.post(route('managers.store'), formData, {
    onSuccess: () => {
      isAddModalOpen.value = false;
    },
  });
};

// Open Edit Modal
const openEditModal = (manager:any) => {
    selectedManager.value = { ...manager };
    delete selectedManager.value.avatar_image;
    page.props.errors = {};
    isEditModalOpen.value = true;
};
const editFormSchema = z
    .object({
        name: z.string().min(3, 'Manager name is required').max(50, 'Too long').describe('Manager name'),
        email: z.string().email('Manager email is required').max(100, 'Too long').describe('Manager email'),
        national_id: z.string().min(1, 'National ID is required').describe('National ID'),
        password: z.string().min(8, 'Password must be at least 8 characters long').optional().describe('Password'),
        password_confirmation: z.string().optional().describe('Password Confirmation'),
        avatar_image: z.instanceof(File).optional().describe('Avatar'),
        })
    .refine((data) => {
        if (data.password && data.password !== data.password_confirmation) {
            return { message: 'Passwords do not match', path: ['password_confirmation'] };
        }
        return true;
    });
const onEditSubmit = (data: any) => {
    const formData = { ...data };
    formData["_method"] = 'PUT';
    router.post(route('managers.update', selectedManager.value?.id), formData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
        },
    });
};



// Open Delete Modal
const openDeleteModal = (manager:any) => {
    selectedManager.value = manager;
    isDeleteModalOpen.value = true;
};

// Confirm Delete
const confirmDelete = () => {
    router.delete(route('managers.destroy', selectedManager.value?.id), {
        preserveState: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        },
    });
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
            <ManageModal v-if="isEditModalOpen" title="Edit Manager" v-model:open="isEditModalOpen" :buttonsVisible="false" :errors="errors">
                <template #description>
                    <Form
                        :schema="editFormSchema"
                        :submitText="'Update Manager'"
                        :initialValues="selectedManager"
                        :fieldConfig="fieldConfig"
                        @submit="onEditSubmit($event)"
                        @cancel="isEditModalOpen = false "
                    >
                    </Form>
                </template>
            </ManageModal>

            <!-- Add Modal -->
            <ManageModal v-if="isAddModalOpen" title="Add Manager"
            v-model:open="isAddModalOpen" :disableEsc="false" :buttonsVisible="false" :errors="errors">
                <template #description>
                    <Form
                        :schema="formSchema"
                        :submitText="'Add'"
                        :initialValues="{}"
                        :fieldConfig="fieldConfig"
                        @submit="onAddSubmit($event);"
                        @cancel="isAddModalOpen = false "
                        >
                    </Form>
                </template>
            </ManageModal>
        </div>
    </AppLayout>
</template>
