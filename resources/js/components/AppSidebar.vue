<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import * as LucideIcons from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
import medicationRoute from '@/routes/medication';
import patientRoute from '@/routes/patient';
import scheduleRoute from '@/routes/practice-schedule';
import medicationCategoryRoute from '@/routes/medication-category';
import queueRoute from '@/routes/queue';
import profile from '@/routes/profile';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LucideIcons.LucideLayoutDashboard,
    },

    {
        title: 'Jadwal',
        href: scheduleRoute.index(),
        icon: LucideIcons.Calendar,
    },

    {
        title: 'Kategori Obat',
        href: medicationCategoryRoute.index(),
        icon: LucideIcons.Layers,
    },

    {
        title: 'Data Obat',
        href: medicationRoute.index(),
        icon: LucideIcons.LayoutGrid,
    },

    {
        title: 'Pasien',
        href: patientRoute.index(),
        icon: LucideIcons.User2,
    },

    {
        title: 'Antrian',
        href: queueRoute.index(),
        icon: LucideIcons.ListEnd,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: LucideIcons.FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: LucideIcons.BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
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
