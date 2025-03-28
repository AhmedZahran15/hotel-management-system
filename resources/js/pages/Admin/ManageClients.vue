<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';

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
const selectedClientId = ref(null);
const isLoading = ref(false);
const form = ref({
    name: '',
    email: '',
    country: '',
    gender: '',
    phones: '',
    avatar_image: null,
});
const params = new URLSearchParams(window.location.search);
const filters = ref({
    name: params.get('filter[name]') || '',
    email: params.get('filter[email]') || '',
    country: params.get('filter[country]')||'',
});
const sorting = ref([
    {
        id: params.get('sort')?.replace('-', '') || '',
        desc: params.get('sort')?.includes('-') || false,
    },
]);
const pagination = ref({
    pageIndex: props.clients.meta.current_page - 1,
    pageSize: props.clients.meta.per_page,
    dataSize: props.clients.meta.total,
});

// Table Columns
const countries = page.props.countries;
const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'name', header: 'Name' },
    { accessorKey: 'email', header: 'Email' },
    { accessorKey: 'country.name', header: 'Country' },
    { accessorKey: 'phones.0.phone', header: 'Phone' },
    { accessorKey: 'gender', header: 'Gender' },
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
                    h(Button, { variant: 'destructive', class: 'mx-1', onClick: () => openDeleteModal(client.id) }, () => 'Remove'),
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

// Open Edit Modal
const openEditModal = (client) => {
    page.props.errors = {};
    form.value = {
        ...client,
        country: typeof client.country === 'object' ? client.country.id : client.country,
        avatar_image: null,
    };
    isEditModalOpen.value = true;
};

// Open Add Modal
const openAddModal = (client) => {
    page.props.errors = {};
    resetAddForm();
    isAddModalOpen.value = true;
};

// Open Delete Modal
const openDeleteModal = (id) => {
    selectedClientId.value = id;
    isDeleteModalOpen.value = true;
};

// Reset Add Form
const resetAddForm = () => {
    form.value = {
        name: '',
        email: '',
        country: '',
        gender: '',
        avatar_image: null,
        password: '',
        password_confirmation: '',
    };
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

// Handle Add Client
const handleAdd = () => {

    const formData = new FormData();
    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null) formData.append(key, form.value[key]);
    });
    router.post(route('clients.store'), formData, {
        onSuccess: () => {
            isAddModalOpen.value = false;
        },

    });
};

// Handle Edit Client
const handleEdit = () => {
    const formData = new FormData();
    formData.append('_method', 'PATCH');
    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null) formData.append(key, form.value[key]);
    });

    // Submit the form
    router.post(`/dashboard/clients/${form.value.id}`, formData, {
        onSuccess: () => {
            isEditModalOpen.value = false;
        },
    });
};

// Confirm Delete
const confirmDelete = () => {
    router.delete(`/dashboard/clients/${selectedClientId.value}`, {
        preserveState: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        },
    });
    isDeleteModalOpen.value = false;
};

// Approve Client
const approveClient = (id) => {
    try {
        isLoading.value = true;
        router.patch(
            `/dashboard/clients/${id}/approve`,
            {},
            {
                onSuccess: () => {
                    isLoading.value = false;
                    successMessage.value = 'Client Has Been Approved Successfully.';
                    errorMessage.value = '';
                    setTimeout(() => {
                        successMessage.value = '';
                    }, 3000);
                },
            },
        );
    } catch (error) {
        isLoading.value = false;
        errorMessage.value = 'Failed to Approve Client.';
        successMessage.value = '';
        setTimeout(() => {
            errorMessage.value = '';
        }, 3000);
    }
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
                :data="
                    props.clients.data
                        .filter((client) => {
                            if (userRole === 'admin' || userRole === 'manager') return true;
                            if (userRole === 'receptionist') return client.approved_by === null;
                            return false;
                        })
                        .map((client) => ({
                            ...client,
                            country: countries.find((country) => +country.id === +client.country) || 'Unknown',
                        }))
                "
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
                    <Button variant="default" @click="openAddModal">Add Client</Button>
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
                :errors="errors">
                <template #description>
                    <form class="flex flex-col gap-4 p-6" @submit.prevent="handleEdit">
                        <div class="flex flex-col gap-1">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="phones">Phone Number</Label>
                            <Input id="phone" v-model="form.phones" type="tel" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="country">Country</Label>
                            <Select v-model="form.country">
                                <SelectTrigger id="country" class="w-full">
                                    <SelectValue placeholder="Select a country" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="country in countries" :key="country.id" :value="country.id">
                                        {{ country.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="gender">Gender</Label>
                            <RadioGroup v-model="form.gender" id="gender" class="flex items-center gap-4" :key="'gender-group-' + isEditModalOpen">
                                <div class="flex items-center">
                                    <RadioGroupItem value="male" id="edit-male" class="mr-2" />
                                    <Label for="edit-male">Male</Label>
                                </div>
                                <div class="flex items-center">
                                    <RadioGroupItem value="female" id="edit-female" class="mr-2" />
                                    <Label for="edit-female">Female</Label>
                                </div>
                            </RadioGroup>
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="avatar">Avatar</Label>
                            <Input id="avatar" type="file" @change="handleImageUpload" />
                        </div>

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

            <ManageModal
                v-if="isAddModalOpen"
                :errors="errors"
                title="Add Client"
                v-model:open="isAddModalOpen"
                :buttonsVisible="false">
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
                            <Label for="phones">Phone Number</Label>
                            <Input id="phone" v-model="form.phones" type="tel" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="country">Country</Label>
                            <Select v-model="form.country">
                                <SelectTrigger id="country" class="w-full">
                                    <SelectValue placeholder="Select a country" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="country in countries" :key="country.id" :value="country.id">
                                        {{ country.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="gender">Gender</Label>
                            <RadioGroup v-model="form.gender" id="gender" class="flex items-center gap-4">
                                <RadioGroupItem value="male" id="male" class="mr-2" />
                                <Label for="male">Male</Label>
                                <RadioGroupItem value="female" id="female" class="mr-2" />
                                <Label for="female">Female</Label>
                            </RadioGroup>
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="avatar">Avatar</Label>
                            <Input id="avatar" type="file" @change="handleImageUpload" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="password">Password</Label>
                            <Input id="password" type="password" v-model="form.password" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button
                                variant="secondary"
                                @click=" () => {isAddModalOpen = false;resetAddForm();}">Close</Button
                            >
                            <Button type="submit">Add</Button>
                        </div>
                    </form>
                </template>
            </ManageModal>
        </div>
    </AppLayout>
</template>
