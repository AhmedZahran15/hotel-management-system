<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BarChart3, BookOpen, CableCar, Calendar, CalendarCheck, CircleUser, Folder, School, UserRoundPlus, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();
const mainNavItems: NavItem[] = [];

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
    href: route('receptionists.index'),
    icon: Users,
};

const manageClientsItem = {
    title: 'Manage Clients',
    href: route('clients.index'),
    icon: UserRoundPlus,
};

const statisticsItem = {
    title: 'Statistics',
    href: route('statistics.index'),
    icon: BarChart3,
};

// Add items based on user role
if (page.props.auth.user.roles.includes('admin')) {
    mainNavItems.push(
        statisticsItem,
        {
            title: 'Manage Managers',
            href: route('managers.index'),
            icon: CircleUser,
        },
        manageReceptionistsItem,
        manageClientsItem,
        {
            title: 'Clients Reservations',
            href: route('reservations.index'),
            icon: Calendar,
        },
        {
            title: 'My Approved Clients',
            href: route('clients.approved'),
            icon: UserRoundPlus,
        },
        manageFloorsItem,
        manageRoomsItem,
    );
}

if (page.props.auth.user.roles.includes('manager')) {
    mainNavItems.push(statisticsItem, manageReceptionistsItem, manageClientsItem, manageFloorsItem, manageRoomsItem);
}

if (page.props.auth.user.roles.includes('receptionist')) {
    mainNavItems.push(
        statisticsItem,
        {
            title: 'Manage Clients',
            href: route('clients.index'),
            icon: UserRoundPlus,
        },
        {
            title: 'My Approved Clients',
            href: route('clients.approved'),
            icon: UserRoundPlus,
        },
        {
            title: 'Clients Reservations',
            href: route('reservations.index'),
            icon: Calendar,
        },
    );
}

if (page.props.auth.user.roles.includes('client')) {
    mainNavItems.push(
        {
            title: 'My Reservations',
            href: route('reservations.index'),
            icon: Calendar,
        },
        {
            title: 'Make Reservation',
            href: route('reservations.make'),
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
                        <Link :href="route('home')">
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
