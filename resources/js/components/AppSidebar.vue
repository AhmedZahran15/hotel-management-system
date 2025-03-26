<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import {type NavItem } from '@/types';
import { Link ,usePage} from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid , CableCar, School ,UserRoundPlus ,CircleUser,Users} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Manage Managers',
        href: '/dashboard/managers',
        icon: CircleUser,
    },
    {
        title: 'Manage Receptionists',
        href: '/dashboard/receptionists',
        icon: Users,
    },
    {
        title: 'Manage Clients',
        href: '/dashboard/clients',
        icon: UserRoundPlus,
    },
];

    if (page.props.auth.user.permissions.find(x=>x ==='manage floors')) {
        mainNavItems.push({
            title: 'Manage Floors',
            href: route("floors.index"),
            icon: CableCar,
        },)
    }
    if (page.props.auth.user.permissions.find(x=>x ==='manage rooms')) {
        mainNavItems.push({
            title: 'Manage Rooms',
            href: route("rooms.index"),
            icon: School,
        },)
    }
    if (page.props.auth.user.role === 'admin') {
    mainNavItems.push(
        {
            title: 'Manage Managers',
            href: '/dashboard/managers',
            icon: LayoutGrid,
        },
        {
            title: 'Manage Receptionists',
            href: '/dashboard/receptionists',
            icon: LayoutGrid,
        },
        {
            title: 'Manage Clients',
            href: '/dashboard/clients',
            icon: LayoutGrid,
        }
    );
};

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
