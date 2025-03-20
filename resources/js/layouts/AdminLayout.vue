<template>
    <div class="flex h-screen">
      <!-- Sidebar -->
      <aside class="w-64 bg-gray-800 text-white">
        <nav class="p-4">
          <ul>
            <li>
              <Link href="/dashboard" class="block py-2 px-4 hover:bg-gray-700">Dashboard</Link>
            </li>
            <li>
              <Link href="/manage-managers" class="block py-2 px-4 hover:bg-gray-700">Manage Managers</Link>
            </li>
            <li>
              <Link href="/manage-receptionists" class="block py-2 px-4 hover:bg-gray-700">Manage Receptionists</Link>
            </li>
            <li>
              <Link href="/manage-clients" class="block py-2 px-4 hover:bg-gray-700">Manage Clients</Link>
            </li>
          </ul>
        </nav>
      </aside>
  
      <!-- Main Content -->
      <main class="flex-1 p-6 bg-gray-100">
        <slot />
      </main>
    </div>
  </template>
  
  <script setup>
  import { Link } from '@inertiajs/vue3';
  </script>
  