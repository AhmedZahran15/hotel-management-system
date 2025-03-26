<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, CableCar, Calendar, CalendarCheck, CircleUser, Folder, LayoutGrid, School, UserRoundPlus, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

// Common menu items definitions
const manageFloorsItem = {
    title: 'Manage Floors',
    href: route('floors.index'),
    icon: CableCar,
};

const manageRoomsItem = {
    title: 'Manage Rooms',
    href: route('rooms.index'),
    icon: School,
};

const manageReceptionistsItem = {
    title: 'Manage Receptionists',
    href: '/dashboard/receptionists',
    icon: Users,
};

const manageClientsItem = {
    title: 'Manage Clients',
    href: '/dashboard/clients',
    icon: UserRoundPlus,
};

// Add items based on user role
if (page.props.auth.user.roles.includes('admin')) {
    mainNavItems.push(
        {
            title: 'Manage Managers',
            href: '/dashboard/managers',
            icon: CircleUser,
        },
        manageReceptionistsItem,
        manageClientsItem,
        manageFloorsItem,
        manageRoomsItem,
    );
}

if (page.props.auth.user.roles.includes('manager')) {
    mainNavItems.push(manageReceptionistsItem, manageClientsItem, manageFloorsItem, manageRoomsItem);
}

if (page.props.auth.user.roles.includes('receptionist')) {
    mainNavItems.push(
        {
            title: 'My Approved Clients',
            href: '/dashboard/approved',
            icon: UserRoundPlus,
        },
        {
            title: 'Clients Reservations',
            href: '/dashboard/reservations',
            icon: Calendar,
        },
    );
}

if (page.props.auth.user.roles.includes('client')) {
    mainNavItems.push(
        {
            title: 'My Reservations',
            href: '/dashboard/my-reservations',
            icon: Calendar,
        },
        {
            title: 'Make Reservation',
            href: '/dashboard/make-reservation',
            icon: CalendarCheck,
        },
    );
}

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="floating">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
