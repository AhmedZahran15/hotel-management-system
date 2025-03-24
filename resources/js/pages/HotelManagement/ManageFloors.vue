<script setup lang="ts">
import type {ColumnDef} from '@tanstack/vue-table'
import { ref, defineProps, h, computed } from "vue";
import DataTable from "@/components/Shared/ManageDataTable.vue";
import Alert from "@/components/Shared/Alert.vue";
import Modal from "@/components/Shared/ManageModal.vue";
import { AlertCircle } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head,usePage } from '@inertiajs/vue3';
import {
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import { toast } from '@/components/ui/toast/use-toast'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import * as z from 'zod'
import { Button } from '@/components/ui/button'
import { vAutoAnimate } from '@formkit/auto-animate/vue'


const breadcrumbs: BreadcrumbItem[] = [
    {
        title :'Manage Floors',
        href: route('floors.index'),
    },
];

const page = usePage();
const props = defineProps(["floors"]);
const errors = computed(()=>page.props.errors);

// Tyoes
type Floor = {
    number:number
    name: string
    manger?:{
        name:string
    }
    roomsCount: number,
    reservedRoomsCount: number,
    availavleRoomsCount: number,
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
            h(Button ,{variant:"default",class:"mx-1", onClick:()=>handleEdit(info.row.original)},()=> "Edit"),
            h(Button ,{variant:"destructive",class:"mx-1",  disabled:info.row.original.roomsCount!=0, onClick:()=>handleDelete(info.row.original)},()=>"Remove")]
    },
]);

//append Manger column in case of Admin (Depending on the data sent from the backend)
if (props.floors?.data?.length > 0 && props.floors.data[0]?.manager) {
    columns.value.splice(2,0,{accessorKey:"manager.name", header:"manger"});
}

// paginate
const fetchPage = (url:string) =>{
    if (url) {
        router.get(url);
    }
}
// Delete
const selectedFloor = ref<Floor|null>(null);
const deleteModalOpen =ref<boolean>(false);

const handleDelete=(floor:Floor)=> {
    selectedFloor.value = floor
    deleteModalOpen.value =true;
}

const deleteConfirmed=()=>{
    if(selectedFloor.value){
        router.delete(route("floors.destroy",selectedFloor.value.number));
        router.get(page.url);
    }
    selectedFloor.value = null;
}

//Edit
const editModalOpen =ref<boolean>(false);
const handleEdit=(floor:Floor)=> {
    selectedFloor.value = floor
    editModalOpen.value =true;
}
const updateConfirmed=()=>{
    if(selectedFloor.value){
        router.put(route("floors.update",selectedFloor.value.number,selectedFloor.value));
        router.get(page.url);
    }
    selectedFloor.value = null;
}

const formSchema = toTypedSchema(z.object({
  username: z.string().min(2).max(50),
}))

const { isFieldDirty, handleSubmit } = useForm({
  validationSchema: formSchema,
})

const onSubmit = handleSubmit((values) => {
  toast({
    title: 'You submitted the following values:',
    description: h('pre', { class: 'mt-2 w-[340px] rounded-md bg-slate-950 p-4' }, h('code', { class: 'text-white' }, JSON.stringify(values, null, 2))),
  })
})


//AlertDismiss
const dismissError=()=>{
    page.props.errors={};
}
</script>

<template>
    <Head title="Manage Floors" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Alert class="w-10/12 mx-auto mt-4"
            v-for="(value,index) of errors" :key="index"  :show="true"
            :variant="'destructive'" :title="index" :message="value" >
            <template v-slot:icon><AlertCircle class="w-4 h-4"/></template>
            <template v-slot:dismissBtn><Button :variant="'destructive'" @click="dismissError">dismiss</Button></template>
        </Alert>
        <div class="flex flex-col flex-1 h-full gap-4 px-4 py-4 rounded-xl">
            <div class="px-6">
                <DataTable  :columns="columns" :data="props.floors.data" />
            </div>
            <div v-if="props.floors?.meta.links?.length>0" class="flex justify-center gap-2 mt-4">
                <Button
                    v-for="(link, index) in props.floors.meta.links"
                    :key="index"
                    :variant="link.active ? 'default' : 'outline'"
                    :disabled="!link.url"
                    @click="fetchPage(link.url)"
                    v-html="link.label"
                />
            </div>
            <Modal v-if=deleteModalOpen :title="'Deleting floor no. '+selectedFloor?.number.toString()"   v-model:open="deleteModalOpen" @confirm="deleteConfirmed">

                <template #description>
                    <div class="flex flex-col gap-2 py-3">
                        <p class="text-lg">Are you sure you want ro delete this floor?</p>
                        <p class="text-xs">Note: Can't delete floor containing floors </p>
                    </div>
                </template>
            </Modal>
            <Modal v-if="editModalOpen" :title="'Updating floor no. '+selectedFloor?.number.toString()" :buttonsVisible="false"
            v-model:open="editModalOpen" @confirm="updateConfirmed" :confirmText="'Update'">
                <template #description>
                    <form class="w-2/3 space-y-6" @submit="onSubmit">
                        <FormField v-slot="{ componentField }" name="username" :validate-on-blur="!isFieldDirty">
                        <FormItem v-auto-animate>
                            <FormLabel>Username</FormLabel>
                            <FormControl>
                            <Input type="text" placeholder="shadcn" v-bind="componentField" />
                            </FormControl>
                            <FormDescription>
                            This is your public display name.
                            </FormDescription>
                            <FormMessage />
                        </FormItem>
                        </FormField>
                        <Button type="submit">Submit</Button>
                    </form>
                </template>
            </Modal>

        </div>
    </AppLayout>
</template>
