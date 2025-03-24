<script setup lang="ts">
import  {useVueTable, getCoreRowModel,   FlexRender} from '@tanstack/vue-table';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/table";

const props = defineProps<{
    columns: any[],
    data:any[]
}>();


const table = useVueTable({
    data: props.data ,
    columns: props.columns,
    getCoreRowModel: getCoreRowModel()

});
</script>
<template>
    <div>
        <div class="border-2 border-gray-500 rounded-lg">
            <Table class="">
            <TableHeader class="text-center">
                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="">
                    <TableHead v-for="header in headerGroup.headers" :key="header.id" class="text-center">
                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <template v-for="row in table.getRowModel().rows" :key="row.id">
                        <TableRow :data-state="row.getIsSelected() && 'selected'" class="text-center">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="row.getIsExpanded()">
                            <TableCell :colspan="row.getAllCells().length">
                                {{ JSON.stringify(row.original) }}
                            </TableCell>
                        </TableRow>
                    </template>
                </template>
                <TableRow v-else>
                    <TableCell :colspan="columns.length" class="h-24 text-center" >
                        No results.
                    </TableCell>
                </TableRow>
            </TableBody>
            </Table>
        </div>
    </div>
</template>
