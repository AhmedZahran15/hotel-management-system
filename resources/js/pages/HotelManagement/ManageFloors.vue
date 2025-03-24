<script setup lang="ts">
import type {ColumnDef} from '@tanstack/vue-table'
import { ref, defineProps, h } from "vue";
import DataTable from "@/components/Shared/ManageDataTable.vue";
import Modal from "@/components/Shared/ManageModal.vue";
import Button from  "@/components/ui/button/Button.vue"
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title :'Manage Floors',
        href: route('floors.index'),
    },
];
const props = defineProps(["floors"]);
console.log(props.floors)
const floors = ref(props.floors?.data ??[]);

type Floor = {
    number:number
    name: string
    manger?:{
        name:string
    }
}

//Columns for DataTable
const columns =ref<ColumnDef<Floor>[]>  ([
    { accessorKey: "number", header: "Floor Number" },
    { accessorKey: "name", header: "Floor Name" },
    { accessorKey: "roomsCount", header: "No of rooms" },
    { accessorKey: "reservedRoomsCount", header: "Reserved" },
    { accessorKey: "availabledRoomsCount", header: "Available" },
    { accessorKey: "Edit", header: "Actions",
        cell: (info)=>[
            h(Button ,{variant:"default",class:"mx-1" ,onClick:()=>handleEdit(info.row.original)},()=> "Edit"),
            h(Button ,{variant:"destructive",class:"mx-1",onClick:()=>handleDelete(info.row.original)},()=>"Remove")]
    },
]);
const selectedFloor = ref<Floor>({number:0, name: 'string'});
const deleteModalOpen =ref<boolean>(false);
const editModalOpen =ref<boolean>(false);

const handleDelete=(floor:Floor)=> {
    console.log(deleteModalOpen.value);
    selectedFloor.value = floor
    deleteModalOpen.value =true;
}
const handleEdit=(floor:Floor)=> {
    selectedFloor.value = floor
    editModalOpen.value =true;
}
const updateConfirmed=()=>{
    router.delete(route("floors.destroy",selectedFloor.value.number))
}
</script>

<template>
    <Head title="Manage Floors" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
            <div class="p-6">
                <DataTable v-if="floors.length" :columns="columns" :data="floors" />
                <p v-else class="text-gray-500">No clients found.</p>
            </div>
            <Modal :title="'Deleting floor no.'+selectedFloor.number.toString()"   v-model:open="deleteModalOpen" @confirm="updateConfirmed">

                <template #description>
                    <div class="flex flex-col gap-2 py-3">
                        <p class="text-lg">Are you sure you want ro delete this floor?</p>
                        <p class="text-xs">Note: Can't delete floor containing floors </p>
                    </div>
                </template>
            </Modal>
        </div>
    </AppLayout>
</template>
