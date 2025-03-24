<script setup lang="ts">
import DataTable from "@/components/Shared/DataTable.vue";

const columns = [
  { accessorKey: "id", header: "ID" },
  { accessorKey: "name", header: "Name" },
  { accessorKey: "email", header: "Email" },
  { accessorKey: "country", header: "Country" },
  { accessorKey: "avatar_image", header: "Avatar" },
];
</script>

<template>
  <DataTable
    fetchUrl="/dashboard/clients"
    deleteUrl="/dashboard/clients"
    :columns="columns"
    @edit="$emit('edit', $event)"
  />
</template>
