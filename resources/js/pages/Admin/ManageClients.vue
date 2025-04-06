<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Form from '@/components/Shared/Form.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Table2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button';
import { extractSorting, formulateURL } from '@/utils/helpers';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import * as z from 'zod';
// Success Message
const successMessage = ref('');

// Breadcrumbs for navigation
const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Clients', href: route('clients.index'), active: true },
];

const props = defineProps(['clients', 'countries']);
const page = usePage();
// User Role
const userRole = page.props.auth.user.roles[0];

const errors = computed(() => page.props.errors);

// State Variables
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedClient = ref(null);
const isLoading = ref(false);
const params = new URLSearchParams(window.location.search);

const filters = ref([
    { column: 'Name', value: params.get('filter[name]') || '', urlName: 'name' },
    { column: 'Email', value: params.get('filter[email]') || '', urlName: 'email' },
    { column: 'Country', value: params.get('filter[country]') || '', urlName: 'country' },
]);

const sorting = ref(extractSorting(params));

const pagination = ref({
    pageIndex: props.clients.meta.current_page - 1,
    pageSize: props.clients.meta.per_page,
    dataSize: props.clients.meta.total,
});

// Table Columns
const columns = [
    { accessorKey: 'id', header: 'ID', sortable: true },
    { accessorKey: 'name', header: 'Name', sortable: true },
    { accessorKey: 'email', header: 'Email', sortable: true },
    { accessorKey: 'country.name', header: 'Country', sortable: true },
    { accessorKey: 'phones.0', header: 'Phone' },
    { accessorKey: 'gender', header: 'Gender', sortable: true },
    {
        accessorKey: 'approved_by',
        header: 'Status',
        cell: (info) => {
            const approvedBy = info.getValue();
            return h(
                'span',
                {
                    class: approvedBy ? 'text-green-500 font-semibold' : 'text-red-500 font-semibold',
                },
                approvedBy ? 'Approved' : 'Pending',
            );
        },
    },
    {
        accessorKey: 'Actions',
        header: 'Actions',
        cell: (info) => {
            const client = info.row.original;
            const buttons = [];

            if (userRole === 'manager' || userRole === 'admin') {
                buttons.push(
                    h(Button, { variant: 'default', class: 'mx-1', onClick: () => openEditModal(client) }, () => 'Edit'),
                    h(Button, { variant: 'destructive', class: 'mx-1', onClick: () => openDeleteModal(client) }, () => 'Remove'),
                    client.approved_by === null
                        ? h(
                              Button,
                              {
                                  variant: 'success',
                                  class: 'mx-1',
                                  onClick: () => approveClient(client.id),
                                  disabled: isLoading.value,
                              },
                              () => 'Approve',
                          )
                        : h(Button, { variant: 'default', class: 'mx-1 bg-gray-400', disabled: true }, () => 'Approved'),
                );
            } else if (userRole === 'receptionist') {
                buttons.push(
                    client.approved_by === null
                        ? h(
                              Button,
                              {
                                  variant: 'success',
                                  class: 'mx-1',
                                  onClick: () => approveClient(client.id),
                                  disabled: isLoading.value,
                              },
                              () => 'Approve',
                          )
                        : h(Button, { variant: 'default', class: 'mx-1 bg-gray-400', disabled: true }, () => 'Approved'),
                );
            }

            return buttons;
        },
    },
];

// Fetch Clients
const fetchClients = () => {
    const params = formulateURL(filters.value, sorting.value, pagination.value);

    router.get(route('clients.index'), Object.fromEntries(params.entries()), {
        preserveScroll: true,
        preserveState: true,
        only: ['clients'],
        onSuccess: () => {
            pagination.value = {
                pageIndex: props.clients.meta.current_page - 1,
                pageSize: props.clients.meta.per_page,
                dataSize: props.clients.meta.total,
            };
        },
    });
};

const formSchema = z
    .object({
        name: z.string().min(3, 'Client name is required').max(50, 'Too long').describe('Client name'),
        email: z.string().email('client email is required').max(100, 'Too long').describe('client email'),
        phone: z.string().regex(new RegExp(/^([+]?[\s0-9]+)?(\d{3}|[(]?[0-9]+[)])?([-]?[\s]?[0-9])+$/)).describe('phone number'),
        country: z.enum(page.props.countries.map((country) => country.name),{ required_error: 'Country is required' },).describe('Country'),
        gender: z.enum(['male', 'female'], { required_error: 'Gender is required' }).describe('Gender'),
        password: z.string().min(8, 'Password must be at least 8 characters long').describe('Password'),
        password_confirmation: z.string().describe('Password Confirmation'),
        avatar_image: z.instanceof(File).optional().describe('Avatar'),
    }).refine((data) => data.password === data.password_confirmation, {
        message: 'Passwords do not match',
        path: ['password_confirmation'],
    });
//ensures the password fields are hidden
const fieldConfig = {
    password_confirmation: {
        inputProps: { type: 'password' },
    },
    password: {
        inputProps: { type: 'password' },
    },
    gender: { component: 'radio' },
    avatar_image: {
        inputProps: { type: 'file', accept: 'image/jpeg, image/jpg' },
        component: 'file',
    },
};

// Handle Add Client
const onAddSubmit = (data: any) => {
    const formData = { ...data };
    formData.country = page.props.countries.find((country) => country.name === data.country).id.toString();
    router.post(route('clients.store'), formData, {
        onSuccess: () => {
            isAddModalOpen.value = false;
        },
    });
};

const editFormSchema = z
    .object({
        name: z.string().min(3, 'Client name is required').max(50, 'Too long').describe('Client name'),
        email: z.string().email('client email is required').max(100, 'Too long').describe('client email'),
        phone: z.string().regex(new RegExp(/^([+]?[\s0-9]+)?(\d{3}|[(]?[0-9]+[)])?([-]?[\s]?[0-9])+$/)).describe('phone number'),
        country: z.enum(page.props.countries.map((country) => country.name),{ required_error: 'Country is required' },).describe('Country'),
        gender: z.enum(['male', 'female'], { required_error: 'Gender is required' }).describe('Gender'),
        password: z.string().min(8, 'Password must be at least 8 characters long').optional().describe('Password'),
        password_confirmation: z.string().optional().describe('Password confirmation'),
        avatar_image: z.instanceof(File).optional().describe('Avatar'),
    }).refine((data) => data.password === data.password_confirmation, {
        message: 'Passwords do not match',
        path: ['password_confirmation'],
    });;


// Open Edit Modal
const openEditModal = (client) => {
    selectedClient.value = { ...client };
    selectedClient.value.country = client.country.name;
    selectedClient.value.phone = client.phones[0];
    isEditModalOpen.value = true;
};

const onEditSubmit = (data: any) => {
    const formData = { ...data };
    formData.country = page.props.countries.find((country) => country.name === data.country).id.toString();

    if (!data.password || data.password === '') {
        delete formData.password;
        delete formData.password_confirmation;
    }
    formData['_method'] = 'PATCH';

    // Submit the form
    router.post(route('clients.update', selectedClient.value.id), formData, {
        onSuccess: () => {
            isEditModalOpen.value = false;
        },
    });
};

// Open Delete Modal
const openDeleteModal = (client) => {
    selectedClient.value = client;
    isDeleteModalOpen.value = true;
};

// Confirm Delete
const confirmDelete = () => {
    router.delete(route('clients.destroy', selectedClient.value.id), {
        preserveState: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        },
    });
    isDeleteModalOpen.value = false;
};

// Approve Client
const approveClient = (id) => {
    isLoading.value = true;
    router.patch(
        route('clients.approve', id),
        {},
        {
            onSuccess: () => {
                isLoading.value = false;
                successMessage.value = 'Client Has Been Approved Successfully.';
                setTimeout(() => {
                    successMessage.value = '';
                }, 3000);
            },
        },
    );
};
</script>

<template>
    <Head title="Manage Clients" />
    <!-- Main Content -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6">
            <div v-if="successMessage" class="rounded bg-green-500 p-4 text-white">
                {{ successMessage }}
            </div>
        </div>
        <div class="px-6">
            <ManageDataTable
                title="Clients"
                :columns="columns"
                :errors="errors"
                :data="props.clients.data"
                :pagination="pagination"
                :filters="filters"
                :manual-pagination="true"
                :manual-sorting="true"
                :manual-filtering="true"
                :sorting="sorting"
                @update:sorting="
                    (newSorting) => {
                        sorting = newSorting;
                        fetchClients();
                    }
                "
                @update:filters="
                    (newFilters) => {
                        filters = newFilters;
                        fetchClients();
                    }
                "
                @update:pagination="
                    (newPagination) => {
                        pagination = newPagination;
                        fetchClients();
                    }
                "
            >
                <template #table-action>
                     <a :href="route('manager.clients.export')"  class="mx-4">
                        <Button v-if="userRole === 'admin'||userRole === 'manager' " variant="default" >
                            <Table2 class="inline me-2" />Export clients
                        </Button>
                    </a>
                    <Button variant="default" @click="isAddModalOpen = true">Add Client</Button>
                </template>
            </ManageDataTable>

            <!-- Delete Modal -->
            <ManageModal v-if="isDeleteModalOpen" title="Deleting Client" v-model:open="isDeleteModalOpen" :buttonsVisible="false">
                <template #description>
                    <p class="text-lg">Are you sure you want to delete this client?</p>
                </template>

                <template #footer>
                    <Button variant="secondary" @click="isDeleteModalOpen = false">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete</Button>
                </template>
            </ManageModal>

            <ManageModal
                v-if="isEditModalOpen"
                title="Edit Client"
                v-model:open="isEditModalOpen"
                :buttonsVisible="false"
                :errors="errors"
                :disableEsc="false"
            >
                <template #description>
                    <Form
                        :schema="editFormSchema"
                        :submitText="'Update'"
                        :initialValues="selectedClient"
                        :fieldConfig="fieldConfig"
                        @submit="onEditSubmit($event);"
                        @cancel="isEditModalOpen = false ">
                    </Form>
                </template>
            </ManageModal>

            <ManageModal
                v-if="isAddModalOpen"
                :errors="errors"
                title="Add Client"
                v-model:open="isAddModalOpen"
                :buttonsVisible="false"
                :disableEsc="false"
            >
                <template #description>
                    <Form
                        :schema="formSchema"
                        :submitText="'Add'"
                        :initialValues="{}"
                        :fieldConfig="fieldConfig"
                        @submit="onAddSubmit($event);"
                        @cancel="isAddModalOpen = false ">
                    </Form>
                </template>
            </ManageModal>
        </div>
    </AppLayout>
</template>
