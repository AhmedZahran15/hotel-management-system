<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, router, usePage } from '@inertiajs/vue3';
import { h, onMounted, ref } from 'vue';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Clients', href: '/dashboard/clients', active: true },
];

const clients = ref([]);
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedClientId = ref(null);
const pagination = ref({ pageIndex: 0, pageSize: 10, total: 0 });
const sorting = ref([]);
const filters = ref({});
const form = ref({ name: '', email: '', country: '', gender: '', avatar_image: null });
const page = usePage();
const countries = page.props.countries;
const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'name', header: 'Name' },
    { accessorKey: 'email', header: 'Email' },
    { accessorKey: 'country.name', header: 'Country' },
    { accessorKey: 'gender', header: 'Gender' },
    {
        accessorKey: 'user.avatar_image',
        header: 'Avatar',
        cell: ({ row }) =>
            h('img', {
                src: row.original.user?.avatar_image || '/default-avatar.jpg',
                alt: 'Avatar',
                class: 'w-12 h-12 rounded-full object-cover',
            }),
    },
    {
        accessorKey: 'Actions',
        header: 'Actions',
        cell: (info) => [
            h(Button, { variant: 'default', class: 'mx-1', onClick: () => openEditModal(info.row.original) }, () => 'Edit'),
            h(Button, { variant: 'destructive', class: 'mx-1', onClick: () => openDeleteModal(info.row.original.id) }, () => 'Remove'),
        ],
    },
];

const fetchClients = async () => {
    router.get(
        '/dashboard/clients',
        {
            page: pagination.value.pageIndex + 1,
            perPage: pagination.value.pageSize,
            sorting: sorting.value,
            filters: filters.value,
        },
        {
            preserveState: true,
            onSuccess: (page) => {
                clients.value = page.props.clients.data;
                pagination.value.total = page.props.clients.total;
            },
        },
    );
};

const openEditModal = (client) => {
    // Normalize gender value to ensure it matches exactly "Male" or "Female"
    let gender = client.gender || '';
    if (gender.toLowerCase() === 'male') gender = 'male';
    if (gender.toLowerCase() === 'female') gender = 'female';

    form.value = {
        id: client.id,
        name: client.name || '',
        email: client.email || '',
        country: typeof client.country === 'object' ? client.country.id : client.country,
        gender: gender,
        avatar_image: null,
        password: '',
        password_confirmation: '',
    };
    isEditModalOpen.value = true;
};

const openDeleteModal = (id) => {
    selectedClientId.value = id;
    isDeleteModalOpen.value = true;
};

const handleFileUpload = (event) => {
    form.value.avatar_image = event.target.files[0];
};

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

const resetEditForm = () => {
    form.value = {
        id: null,
        name: '',
        email: '',
        country: '',
        gender: '',
        avatar_image: null,
        password: '',
        password_confirmation: '',
    };
};

const handleAdd = () => {
    const formData = new FormData();
    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null) formData.append(key, form.value[key]);
    });
    router.post('/dashboard/clients', formData, {
        onSuccess: () => {
            isAddModalOpen.value = false;
            fetchClients();
            resetAddForm();
        },
        onerror: (page) => {
            console.log(page.props.errors);
        },
    });
};

const handleEdit = async () => {
    const formData = new FormData();
    // Add the method override for PATCH
    formData.append('_method', 'PATCH');

    // Process each form field
    Object.keys(form.value).forEach((key) => {
        // Handle special case for avatar_image (only add if file is selected)
        if (key === 'avatar_image') {
            if (form.value.avatar_image) {
                formData.append('avatar_image', form.value.avatar_image);
            }
        }
        // For password fields, only add if they're not empty
        else if ((key === 'password' || key === 'password_confirmation') && form.value[key] === '') {
            // Skip empty password fields
        }
        // For all other fields, add them if they have a value
        else if (form.value[key] !== null && form.value[key] !== undefined) {
            formData.append(key, form.value[key]);
        }
    });

    // Submit the form
    await router.post(`/dashboard/clients/${form.value.id}`, formData, {
        onSuccess: () => {
            isEditModalOpen.value = false;
            fetchClients();
        },
        onError: (errors) => {
            console.error('Edit form submission errors:', errors);
        },
    });
};

const confirmDelete = async () => {
    await router.delete(`/dashboard/clients/${selectedClientId.value}`, { preserveState: true, onSuccess: fetchClients });
    isDeleteModalOpen.value = false;
};

onMounted(fetchClients);
</script>

<template>
    <Head title="Manage Clients" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6">
            <ManageDataTable
                title="Clients"
                :columns="columns"
                :data="
                    clients.map((client) => ({
                        ...client,
                        country: countries.find((country) => +country.id === +client.country) || 'Unknown',
                    }))
                "
                :pagination="pagination"
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
                @update:open="
                    (val) => {
                        if (!val) resetEditForm();
                    }
                "
            >
                <template #description>
                    <form class="flex flex-col gap-4 p-6" @submit.prevent="handleEdit">
                        <div class="flex flex-col gap-1">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" required />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" required />
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
                            <!-- Add a key to force re-render when form changes -->
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
                            <Input id="avatar" type="file" @change="handleFileUpload" />
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
                            <Button
                                variant="secondary"
                                @click="
                                    () => {
                                        isEditModalOpen = false;
                                        resetEditForm();
                                    }
                                "
                                >Close</Button
                            >
                            <Button type="submit">Update</Button>
                        </div>
                    </form>
                </template>
            </ManageModal>

            <ManageModal
                v-if="isAddModalOpen"
                title="Add Client"
                v-model:open="isAddModalOpen"
                :buttonsVisible="false"
                @update:open="
                    (val) => {
                        if (!val) resetAddForm();
                    }
                "
            >
                <template #description>
                    <form class="flex flex-col gap-4 p-6" @submit.prevent="handleAdd">
                        <div class="flex flex-col gap-1">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" required />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" required />
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
                            <Input id="avatar" type="file" @change="handleFileUpload" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="password">Password</Label>
                            <Input id="password" type="password" v-model="form.password" required />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input id="password_confirmation" type="password" v-model="form.password_confirmation" required />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button
                                variant="secondary"
                                @click="
                                    () => {
                                        isAddModalOpen = false;
                                        resetAddForm();
                                    }
                                "
                                >Close</Button
                            >
                            <Button type="submit">Add</Button>
                        </div>
                    </form>
                </template>
            </ManageModal>
        </div>
    </AppLayout>
</template>
