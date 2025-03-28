<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { FlexRender, getCoreRowModel, getFilteredRowModel, getSortedRowModel, useVueTable } from '@tanstack/vue-table';
import { ArrowDown, ArrowUp } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    columns: any[];
    data: any[];
    sorting?: { id: string; desc: boolean }[];
    filters?: any;
    pagination?: { pageIndex: number; pageSize: number; dataSize: number };
}>();

const emit = defineEmits(['update:sorting', 'update:filters', 'update:pagination']);

const sorting = ref(props.sorting || []);
const filters = ref(props.filters || {});
const pagination = ref(props.pagination || { pageIndex: 0, pageSize: 10, dataSize: 0 });
const links = computed(() => Array.from({ length: Math.ceil(props.pagination?.dataSize / pagination.value.pageSize) }, (_, i) => i + 1));

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
    },
});

watch(sorting, (newSorting) => emit('update:sorting', newSorting), { deep: true });
watch(pagination, (newPagination) => emit('update:pagination', newPagination), { deep: true });

const toggleSort = (columnId: string) => {
    if (!Object.keys(filters.value).includes(columnId)) return;

    const existingIndex = sorting.value.findIndex((s) => s.id === columnId);
    pagination.value.pageIndex = 0;

    if (existingIndex === -1) {
        sorting.value.push({ id: columnId, desc: false });
    } else if (!sorting.value[existingIndex].desc) {
        sorting.value[existingIndex].desc = true;
    } else {
        sorting.value.splice(existingIndex, 1);
    }
};
</script>

<template>
    <div class="w-full overflow-hidden">
        <!-- Filters -->
        <div class="flex flex-col gap-4 py-4">
            <div class="grid w-full gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="(value, column) in filters" :key="column" class="flex items-center gap-3 min-w-0">
                    <Label class="text-left font-bold">{{ column }}:</Label>
                    <Input
                        type="text"
                        v-model="filters[column]"
                        :placeholder="'Filter ' + column + '...'"
                        class="w-2/3 flex-grow shadow-md"
                        @keyup.enter="pagination.pageIndex = 0; emit('update:filters', filters)"
                    />
                </div>
            </div>

            <div class="flex flex-wrap justify-between items-center gap-4">
                <div v-show="Object.keys(filters).length > 0" class="flex gap-4 flex-wrap">
                    <Button class="px-6 sm:px-16 whitespace-nowrap" @click="pagination.pageIndex = 0; emit('update:filters', filters)">Filter</Button>
                    <Button class="px-6 sm:px-16 whitespace-nowrap" variant="destructive" @click="pagination.pageIndex = 0; Object.keys(filters).forEach((key) => (filters[key] = '')); emit('update:filters', filters)">Clear</Button>
                </div>
                <div class="flex justify-end w-full sm:w-auto">
                    <slot name="table-action"></slot>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="border-2 border-gray-500 rounded-lg overflow-x-auto">
            <Table class="w-full min-w-max">
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="cursor-pointer text-center px-4 py-2 transition hover:bg-gray-100 whitespace-nowrap"
                            @click="toggleSort(header.column.id)"
                        >
                            <span class="flex items-center justify-center gap-1">
                                <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                <span v-if="sorting.find((s) => s.id === header.column.id)">
                                    <ArrowUp class="h-4 w-4 text-gray-500" v-if="!sorting.find((s) => s.id === header.column.id)?.desc" />
                                    <ArrowDown class="h-4 w-4 text-gray-500" v-else />
                                </span>
                            </span>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="text-center px-2 py-2">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center text-gray-500">No results found.</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex flex-wrap justify-center gap-2 flex-nowrap overflow-x-auto">
            <Button @click="pagination.pageIndex -= 1" :disabled="pagination.pageIndex === 0" class="rounded border px-3 py-1">Prev</Button>
            <Button v-for="link in links" :key="link" @click="pagination.pageIndex = link - 1" :class="['rounded border px-3 py-1', pagination.pageIndex === link - 1 ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300']">{{ link }}</Button>
            <Button @click="pagination.pageIndex += 1" :disabled="pagination.pageIndex === links.length - 1" class="rounded border px-3 py-1">Next</Button>
        </div>
    </div>
</template>
