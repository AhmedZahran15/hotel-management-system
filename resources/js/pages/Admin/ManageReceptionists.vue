<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ManageDataTable from '@/components/Shared/ManageDataTable.vue';
import ManageModal from '@/components/Shared/ManageModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router, usePage } from '@inertiajs/vue3';
import { h, onMounted, ref, computed } from 'vue';

const successMessage = ref('');
const errorMessage = ref('');
const loggedInUserId = ref(null);
const loggedInUserRole = ref('');

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Receptionists', href: route('receptionists.index'), active: true },
];

const props = defineProps(['receptionists']);
const page = usePage();
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedReceptionistId = ref(null);
const form = ref({ id: null, name: '', email: '', password: '', password_confirmation: '', national_id: '', avatar_image: null });

const errors = computed(() => page.props.errors);
const params = new URLSearchParams(window.location.search);
const filters = ref({
    name: params.get('filter[name]') || '',
    email: params.get('filter[email]'),
});
const sorting = ref([
    {
        id: params.get('sort')?.replace('-', '') || '',
        desc: params.get('sort')?.includes('-') || false,
    },
]);
const pagination = ref({
    pageIndex: props.receptionists.meta.current_page - 1,
    pageSize: props.receptionists.meta.per_page,
    dataSize: props.receptionists.meta.total,
});

const user = page.props.auth.user;
const isAdmin = user?.roles.includes('admin');

const columns = [
    { accessorKey: 'name', header: 'Name' },
    { accessorKey: 'email', header: 'Email' },
    {
        accessorKey: 'created_at',
        header: 'Created At',
        cell: (info) => new Date(info.getValue()).toLocaleString(),
    },
    ...(isAdmin
        ? [
            {
                accessorKey: 'creator',
                header: 'Manager Creator',
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
            const isAdmin = user?.roles.includes('admin');
            const isManagerAndCreator = user?.roles.includes('manager') && user.id === info.row.original.creator_user_id;

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
                        onClick: () => openDeleteModal(info.row.original.id),
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
                              disabled: !(isAdmin || isManagerAndCreator),
                          },
                          () => 'Unban',
                      )
                    : h(
                          Button,
                          {
                              variant: 'default',
                              class: 'mx-1 bg-yellow-500 hover:bg-yellow-600 text-white',
                              onClick: () => handleBan(info.row.original.id),
                              disabled: !(isAdmin || isManagerAndCreator),
                          },
                          () => 'Ban',
                      ),
            ];
        },
    },
];

const fetchReceptionists = async () => {
    const params = new URLSearchParams();

    if (filters.value.room_price) {
        params.append('filter[room_price]', (filters.value.room_price * 100).toString());
    }

    Object.entries(filters.value).forEach(([key, value]) => {
        if (value && key !== 'room_price') params.append(`filter[${key}]`, value);
    });

    if (sorting.value.length > 0) {
        const sortString = sorting.value.map((s) => (s.desc ? `-${s.id}` : s.id)).join(',');
        params.append('sort', sortString);
    }
    params.append('page', (pagination.value.pageIndex + 1).toString());
    params.append('perPage', pagination.value.pageSize.toString());

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

const openEditModal = (receptionist) => {
    form.value = {
        id: receptionist.id,
        name: receptionist.name || '',
        email: receptionist.email || '',
        national_id: receptionist.profile?.national_id || '',
        avatar_image: null,
    };
    isEditModalOpen.value = true;
};

const openDeleteModal = (id) => {
    selectedReceptionistId.value = id;
    isDeleteModalOpen.value = true;
};

const handleFileUpload = (event) => {
    form.value.avatar_image = event.target.files[0];
};

const handleAdd =  () => {

    const formData = new FormData();
    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null) formData.append(key, form.value[key]);
    });
     router.post('/dashboard/receptionists', formData, {
        onSuccess: () => {
            isAddModalOpen.value = false;
            fetchReceptionists();
        },
    });
};

const handleEdit = async () => {
    const formData = new FormData();
    formData.append('_method', 'PATCH');
    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null) formData.append(key, form.value[key]);
    });
    router.post(`/dashboard/receptionists/${form.value.id}`, formData, {
        onSuccess: () => {
            isEditModalOpen.value = false;
            fetchReceptionists();
        },
    });
};

const confirmDelete = async () => {
    await router.delete(`/dashboard/receptionists/${selectedReceptionistId.value}`, {
        preserveState: true,
        onSuccess: fetchReceptionists,
    });
    isDeleteModalOpen.value = false;
};

const handleBan = async (id) => {
    try {
        await router.post(route('ban', id));

        successMessage.value = 'Receptionist has been banned successfully.';
        errorMessage.value = '';
        setTimeout(() => {
            successMessage.value = '';
        }, 5000);
    } catch (error) {
        errorMessage.value = 'Failed to ban receptionist.';
        successMessage.value = '';
        setTimeout(() => {
            errorMessage.value = '';
        }, 5000);
    }
};

const handleUnban = async (id) => {
    try {
        await router.post(`/dashboard/receptionists/${id}/unban`);
        successMessage.value = 'Receptionist has been unbanned successfully.';
        errorMessage.value = '';
        setTimeout(() => {
            successMessage.value = '';
        }, 5000);
    } catch (error) {
        errorMessage.value = 'Failed to unban receptionist.';
        successMessage.value = '';
        setTimeout(() => {
            errorMessage.value = '';
        }, 5000);
    }
};

</script>

<template>
    <Head title="Manage Receptionists" />
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
                    <Button variant="default" @click="isAddModalOpen = true">Add Receptionist</Button>
                </template>
            </ManageDataTable>

            <!-- Add Modal -->
            <ManageModal v-if="isAddModalOpen" title="Add Receptionist" v-model:open="isAddModalOpen" :buttonsVisible="false">
                <template #description>
                    <form class="flex flex-col gap-4 p-6" @submit.prevent="handleAdd">
                        <div class="flex flex-col gap-1">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name"  />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email"  />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="national_id">National ID</Label>
                            <Input id="national_id" v-model="form.national_id"  />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="avatar">Avatar</Label>
                            <Input id="avatar" type="file" @change="handleFileUpload" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="password">Password</Label>
                            <Input id="password" v-model="form.password" type="password"  />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input id="password_confirmation" v-model="form.password_confirmation" type="password"  />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button variant="secondary" @click="isAddModalOpen = false">Close</Button>
                            <Button type="submit">Add</Button>
                        </div>
                    </form>
                </template>
            </ManageModal>

            <!-- Edit Modal -->
            <ManageModal v-if="isEditModalOpen" title="Edit Receptionist" v-model:open="isEditModalOpen" :buttonsVisible="false">
                <template #description>
                    <form class="flex flex-col gap-4 p-6" @submit.prevent="handleEdit">
                        <div class="flex flex-col gap-1">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email"  />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="national_id">National ID</Label>
                            <Input id="national_id" v-model="form.national_id" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <Label for="avatar">Avatar</Label>
                            <Input id="avatar" type="file" @change="handleFileUpload" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button variant="secondary" @click="isEditModalOpen = false">Close</Button>
                            <Button type="submit">Update</Button>
                        </div>
                    </form>
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
