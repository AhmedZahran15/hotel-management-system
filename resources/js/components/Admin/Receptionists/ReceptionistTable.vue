<script setup lang="ts">
import DataTable from "@/components/Shared/DataTable.vue";

const columns = [
  { accessorKey: "id", header: "ID" },
  { accessorKey: "name", header: "Name" },
  { accessorKey: "email", header: "Email" },
  { accessorKey: "national_id", header: "National ID" },
  { accessorKey: "avatar_image", header: "Avatar" },
];
</script>

<template>
  <DataTable
    fetchUrl="/dashboard/receptionists"
    deleteUrl="/dashboard/receptionists"
    :columns="columns"
    @edit="$emit('edit', $event)"
  />
</template>
