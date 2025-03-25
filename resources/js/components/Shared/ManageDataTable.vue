<script setup lang="ts">
import {
    useVueTable,
    getCoreRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    FlexRender
} from '@tanstack/vue-table';
import { ref, watch, computed } from "vue";
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/table";
import { ArrowUp, ArrowDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    columns: any[],
    data: any[],
    sorting?: { id: string; desc: boolean }[],
    filters?: Record<string, string>,
    pagination?: { pageIndex: number; pageSize: number }
}>();

const emit = defineEmits(["update:sorting", "update:filters", "update:pagination"]);

const sorting = ref(props.sorting || []);
const filters = ref({ ...props.filters });
const pagination = ref(props.pagination || { pageIndex: 0, pageSize: 10 });

const totalPages = computed(() => Math.ceil(props.data.length / pagination.value.pageSize));

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

watch(sorting, (newSorting) => emit("update:sorting", newSorting), { deep: true });
watch(filters, (newFilters) => emit("update:filters", newFilters), { deep: true });
watch(pagination, (newPagination) => emit("update:pagination", newPagination), { deep: true });

// Sorting toggle function
const toggleSort = (columnId: string) => {
    const existingIndex = sorting.value.findIndex((s) => s.id === columnId);
    if (existingIndex !== -1) {
        sorting.value[existingIndex].desc = !sorting.value[existingIndex].desc;
    } else {
        sorting.value = [{ id: columnId, desc: false }];
    }
};
</script>

<template>
    <div>
        <!-- Filters -->
        <div class="flex flex-col py-2 gap-3">
            <div class="flex flex-wrap gap-4 w-full">
                <div v-for="(value, column) in filters" :key="column" class="flex items-center gap-4 xs:w-full lg:w-1/2 xl:w-1/3 flex-grow">
                    <Label class="font-bold">{{ column }}:</Label>
                    <div class="flex-grow"></div>
                    <Input
                        type="text"
                        v-model="filters[column]"
                        :placeholder="'Filter ' + column + '...'"
                        class="max-w-md"
                        @keyup.enter="emit('update:filters', filters)"
                    />
                </div>
            </div>
            <div class="flex gap-4">
                <Button class="px-16" @click="emit('update:filters', filters)">Filter</Button>
                <Button class="px-16" variant="destructive"
                        @click="Object.keys(filters).forEach(key => filters[key] = ''); emit('update:filters', filters)">
                    Clear
                </Button>
            </div>
            <div class="flex justify-end">
                <slot name="table-action"></slot>
            </div>
        </div>

        <!-- Table -->
        <div class="border-2 border-gray-500 rounded-lg">
            <Table>
                <TableHeader>
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
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center">
                            No results found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex gap-2 justify-center mt-4">
            <button @click="pagination.pageIndex -= 1"
                :disabled="pagination.pageIndex === 0"
                class="px-3 py-1 border rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
                Prev
            </button>
            <button
                v-for="page in totalPages"
                :key="page"
                @click="pagination.pageIndex = page - 1"
                :class="['px-3 py-1 border rounded', pagination.pageIndex === page - 1 ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300']">
                {{ page }}
            </button>
            <button @click="pagination.pageIndex += 1"
                :disabled="pagination.pageIndex === totalPages - 1"
                class="px-3 py-1 border rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
                Next
            </button>
        </div>
    </div>
</template>
