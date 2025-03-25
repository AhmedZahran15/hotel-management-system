<script setup lang="ts">
import {
    useVueTable,
    getCoreRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    FlexRender
} from '@tanstack/vue-table';
import { ref, watch } from "vue";
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/table";
import { ArrowUp, ArrowDown } from 'lucide-vue-next';
import { Button,} from '@/components/ui/button';
import { Input} from '@/components/ui/input';

const props = defineProps<{
    columns: any[],
    data: any[],
    sorting?: { id: string; desc: boolean }[],
    filters?: any,
    pagination?: { pageIndex: number; pageSize: number }
}>();

const emit = defineEmits(["update:sorting", "update:filters", "update:pagination"]);

const sorting = ref(props.sorting || []);
const filters = ref(props.filters || {});
const pagination = ref(props.pagination || { pageIndex: 0, pageSize: 10 });
const totalPages = ref(props.data.length/pagination.value.pageSize +1);
//const filters = ref([{ table: "", column: "", value: "" }]);

const table = useVueTable({
    get data() {
    return props.data;
  },
    columns: props.columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    manualSorting: true,
    manualFiltering: true,
    manualPagination: true,
    state: {
        sorting: sorting.value,
        filters: filters.value,
        pagination: pagination.value,
    },
});

watch(sorting, (newSorting:SortingValue[]) => emit("update:sorting", newSorting), { deep: true });
watch(pagination, (newPagination: []) => emit("update:pagination", newPagination), { deep: true });

// Sorting toggle function
const toggleSort = (columnId: string) => {
    // prevent
    if(!Object.keys(filters.value).find(x=>x==columnId))return;
    const existingIndex = sorting.value.findIndex((s: SortingValue) => s.id === columnId);

    if (existingIndex !== -1) {
        // If column is already sorted, toggle between ascending, descending, and removing sorting
        if (sorting.value[existingIndex].desc) {
            sorting.value.splice(existingIndex, 1); // Remove sorting if already descending
        } else {
            sorting.value[existingIndex].desc = true; // Switch to descending
        }
    } else {
        sorting.value = [{ id: columnId, desc: false }]; // Default: Ascending
    }
};



</script>

<template>
    <div>
        <div class="flex flex-col py-2 gap-1 ">
            <div class="flex flex-wrap gap-4  w-full">
                <div v-for="(data, columnIndex) in filters" :key="columnIndex" class="w-full sm:w-1/1 lg:w-1/2 xl:w-1/3 flex-grow">
                    <Input
                        type="text"
                        v-model="filters[columnIndex]"
                        :placeholder='"Filter " + columnIndex + "..."'
                        class="w-full"
                        @keyup.enter="emit('update:filters', filters)"
                    />
                </div>
            </div>
            <div class="flex items-center py-4 gap-4 justify-between">
            <slot class="flex-grow" name = "table-action"></slot>
            <Button class="bg-blue-500 hover:bg-blue-600 flex-grow" @click="emit('update:filters', filters)" >Filter</Button>
            <Button class="bg-red-500 hover:bg-red-600 flex-grow"
            @click="Object.keys(filters).forEach(key => filters[key] = ''); emit('update:filters', filters)" >Clear</Button>
            </div>
        </div>

        <div class="border-2 border-gray-500 rounded-lg">
            <Table>
                <TableHeader class="text-center">
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="text-center cursor-pointer select-none transition hover:bg-gray-100"
                            @click="toggleSort(header.column.id)"
                        >
                            <span class="flex items-center justify-center gap-1">
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                                <!-- Sorting Indicators -->
                                <span v-if="sorting.find(s => s.id === header.column.id)">
                                    <ArrowUp class="h-4 w-4 text-gray-500" v-if="!sorting.find(s => s.id === header.column.id)?.desc" />
                                    <ArrowDown class="h-4 w-4 text-gray-500" v-else />
                                </span>
                            </span>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <template v-for="row in table.getRowModel().rows" :key="row.id">
                            <TableRow class="text-center">
                                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </TableCell>
                            </TableRow>
                        </template>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center">
                            No results found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
         <div class="flex gap-2 justify-center mt-4">
        <!-- Previous Button -->
        <button @click="pagination.pageIndex -= 1"
            :disabled="pagination.pageIndex === 0"
            class="px-3 py-1 border rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
            Prev
        </button>

        <!-- Page Number Buttons -->
        <button
            v-for="page in totalPages"
            :key="page"
            @click="pagination.pageIndex = page - 1"
            :class="['px-3 py-1 border rounded', pagination.pageIndex === page - 1 ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300']">
            {{ page }}
        </button>

        <!-- Next Button -->
        <button @click="pagination.pageIndex += 1"
            :disabled="pagination.pageIndex === totalPages - 1"
            class="px-3 py-1 border rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
            Next
        </button>
    </div>
    </div>
</template>
