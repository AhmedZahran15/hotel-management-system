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
    meta?: { current_page: number; per_page: number; total: number; from: number; to: number; last_page: number; links: any };
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

watch(sorting, (newSorting) => {
    pagination.value.pageIndex = 1;
    emit('update:sorting', newSorting);
}, { deep: true });

watch(pagination, (newPagination) => emit('update:pagination', newPagination), { deep: true });

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
        <div class="flex flex-col gap-4 py-4">
            <div class="grid w-full gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="(value, column) in filters" :key="column" class="flex items-center gap-3">
                    <Label class="w-1/3 text-right font-bold">{{ column }}:</Label>
                    <Input
                        type="text"
                        v-model="filters[column]"
                        :placeholder="'Filter ' + column + '...'"
                        class="w-2/3 flex-grow shadow-md"
                        @keyup.enter="pagination.pageIndex = 0; emit('update:filters', filters)"
                    />
                </div>
            </div>

            <div class="flex flex-wrap gap-4 justify-between">
                <div class="flex gap-4">
                    <Button class="px-6 sm:px-16" @click="pagination.pageIndex = 0; emit('update:filters', filters);">Filter</Button>
                    <Button class="px-6 sm:px-16" variant="destructive" @click="Object.keys(filters).forEach((key) => (filters[key] = '')); emit('update:filters', filters);">Clear</Button>
                </div>
                <div class="flex justify-end w-full sm:w-auto">
                    <slot name="table-action"></slot>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg border-2 border-gray-500 overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id" class="cursor-pointer text-center px-4 py-2 transition hover:bg-gray-100" @click="toggleSort(header.column.id)">
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
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-2">
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
        <div class="mt-4 flex flex-wrap justify-center gap-2">
            <button @click="pagination.pageIndex -= 1" :disabled="pagination.pageIndex === 0" class="rounded border bg-gray-200 px-3 py-1 hover:bg-gray-300 disabled:opacity-50">Prev</button>
            <button v-for="link in links" :key="link" @click="pagination.pageIndex = link - 1" :class="['rounded border px-3 py-1 transition', pagination.pageIndex === link - 1 ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300']">{{ link }}</button>
            <button @click="pagination.pageIndex += 1" :disabled="pagination.pageIndex === links.length - 1" class="rounded border bg-gray-200 px-3 py-1 hover:bg-gray-300 disabled:opacity-50">Next</button>
        </div>
    </div>
</template>
