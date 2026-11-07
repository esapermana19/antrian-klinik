<script setup lang="ts">
import { ref } from 'vue';
import { AnimatePresence, motion } from 'motion-v';
import {
    Menu,
    X,
    LayoutDashboard,
    ListChecks,
    Users,
    Ticket,
    TicketCheck,
    Pill,
    Layers,
    Calendar,
    LogOut,
    Activity,
    Tv,
    Settings,
} from 'lucide-vue-next';
import { Link, usePage, router } from '@inertiajs/vue3';
import { dashboard, logout } from '@/routes';
import medicationRoute from '@/routes/medication';
import patientRoute from '@/routes/patient';
import scheduleRoute from '@/routes/practice-schedule';
import medicationCategoryRoute from '@/routes/medication-category';
import queueRoute from '@/routes/queue';
import queueManagement from '@/routes/queue-management';
import myQueue from '@/routes/my-queue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { computed } from 'vue';
import SpringModal from '@/components/SpringModal.vue';
import { Button } from '@/components/ui/button';

const { isCurrentUrl } = useCurrentUrl();
const isSidebarOpen = ref(false);
const showLogoutModal = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
    isSidebarOpen.value = false;
};

// macOS / iOS Inertial Slide-in Physics (Critically Damped - No Overshoot)
const sidebarSpring = {
    type: 'spring',
    stiffness: 400,
    damping: 38,
    mass: 1,
};

const navItems = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutDashboard,
    },
    {
        title: 'Kelola Antrian',
        href: queueManagement.index(),
        icon: ListChecks,
        adminOnly: true,
    },
    {
        title: 'Data Pasien',
        href: patientRoute.index(),
        icon: Users,
        adminOnly: false,
    },
    {
        title: 'Ambil Antrian',
        href: queueRoute.index(),
        icon: Ticket,
        adminOnly: false,
    },
    {
        title: 'Antrian Saya',
        href: myQueue.index(),
        icon: TicketCheck,
        adminOnly: false,
    },
    {
        title: 'Data Obat',
        href: medicationRoute.index(),
        icon: Pill,
        adminOnly: true,
    },
    {
        title: 'Kategori Obat',
        href: medicationCategoryRoute.index(),
        icon: Layers,
        adminOnly: true,
    },
    {
        title: 'Jadwal Praktik',
        href: scheduleRoute.index(),
        icon: Calendar,
        adminOnly: true,
    },
];

const page = usePage();
const userRole = computed(() => (page.props.auth as any)?.user?.role);
const userName = computed(() => (page.props.auth as any)?.user?.name || 'Pengguna');

const filteredNavItems = computed(() => {
    if (userRole.value === 'administrator') {
        return navItems;
    }
    return navItems.filter((item) => !item.adminOnly);
});

const handleLogout = () => {
    showLogoutModal.value = false;
    router.post(logout(), {}, {
        onFinish: () => {
            router.flushAll();
        },
    });
};
</script>

<template>
    <div class="relative min-h-screen w-full bg-slate-50/70 text-slate-900 overflow-x-hidden flex flex-col">
        <!-- Top App Bar -->
        <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-slate-200/80 bg-white/95 backdrop-blur-md px-4 md:px-6">
            <div class="flex items-center gap-3">
                <!-- Hamburger Trigger for Smooth Slide-in Sidebar -->
                <button
                    type="button"
                    @click="toggleSidebar"
                    class="flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition active:scale-95 cursor-pointer shadow-2xs"
                    title="Buka Navigasi"
                >
                    <Menu class="size-4.5" />
                </button>

                <!-- Clinic Brand -->
                <Link :href="dashboard()" class="flex items-center gap-2">
                    <div class="flex size-7 items-center justify-center rounded-md bg-indigo-600 text-white shadow-2xs">
                        <Activity class="size-4" />
                    </div>
                    <span class="text-sm font-bold tracking-tight text-slate-900">MEDIX KLINIK</span>
                    <span class="text-[10px] text-slate-400 font-medium px-2 py-0.5 rounded-md bg-slate-100 hidden sm:inline-block">
                        Sistem Antrean
                    </span>
                </Link>
            </div>

            <!-- Right Controls -->
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" as-child class="h-8 text-xs border-slate-200">
                    <Link href="/display-queue" target="_blank" class="gap-1.5 text-slate-700">
                        <Tv class="size-3.5 text-indigo-600" />
                        <span class="hidden sm:inline">Layar TV</span>
                    </Link>
                </Button>

                <div class="text-xs text-slate-600 font-medium pl-1">
                    Halo, <span class="text-slate-900 font-semibold">{{ userName }}</span>
                </div>
            </div>
        </header>

        <!-- Slide-in Sidebar with Motion-V Physics -->
        <AnimatePresence>
            <!-- Backdrop Overlay (Fade-in/out + Click to dismiss) -->
            <motion.div
                v-if="isSidebarOpen"
                key="sidebar-overlay"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :exit="{ opacity: 0 }"
                :transition="{ duration: 0.22, ease: 'easeInOut' }"
                class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-2xs cursor-pointer"
                @click="closeSidebar"
            />

            <!-- Motion Aside (Smooth Slide-in from -100% to 0%) -->
            <motion.aside
                v-if="isSidebarOpen"
                key="sidebar-drawer"
                :initial="{ x: '-100%' }"
                :animate="{ x: '0%' }"
                :exit="{ x: '-100%', transition: { duration: 0.2, ease: [0.4, 0, 1, 1] } }"
                :transition="sidebarSpring"
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col justify-between border-r border-slate-200 bg-white shadow-2xl select-none"
            >
                <!-- Top Brand & Close -->
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-xs">
                            <Activity class="size-4.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-900">Sistem Antrean</h3>
                            <p class="text-[11px] text-slate-400">Navigasi Klinik</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="closeSidebar"
                        class="rounded-md p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <!-- Nav Menu List -->
                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                    <p class="px-2 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Menu Utama
                    </p>
                    <Link
                        v-for="item in filteredNavItems"
                        :key="item.title"
                        :href="item.href"
                        @click="closeSidebar"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-medium transition-colors"
                        :class="isCurrentUrl(item.href)
                            ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-2xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                    >
                        <component :is="item.icon" class="size-4 shrink-0" />
                        <span class="truncate">{{ item.title }}</span>
                    </Link>
                </nav>

                <!-- Bottom Footer (Settings & Logout) -->
                <div class="p-3 border-t border-slate-100 bg-slate-50/60 space-y-1">
                    <Link
                        href="/settings/profile"
                        @click="closeSidebar"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition"
                    >
                        <Settings class="size-4" />
                        Pengaturan Profil
                    </Link>

                    <button
                        type="button"
                        @click="showLogoutModal = true"
                        class="w-full flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                    >
                        <LogOut class="size-4" />
                        Keluar Akun
                    </button>
                </div>
            </motion.aside>
        </AnimatePresence>

        <!-- Main Content Area -->
        <main class="flex-1 pb-10">
            <slot />
        </main>

        <!-- Spring / Bounce Modal Konfirmasi Logout -->
        <SpringModal
            :open="showLogoutModal"
            @update:open="showLogoutModal = $event"
            title="Konfirmasi Keluar Akun"
            description="Apakah Anda yakin ingin keluar dari sistem antrean ini? Anda perlu memasukkan kredensial login kembali."
        >
            <div class="text-xs text-slate-600 leading-relaxed py-1">
                Sesi pemeriksaan dan antrean aktif akan tetap tersimpan di server klinik.
            </div>

            <template #footer>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-8 text-xs border-slate-200"
                    @click="showLogoutModal = false"
                >
                    Batal
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    class="h-8 text-xs bg-rose-600 hover:bg-rose-700 text-white"
                    @click="handleLogout"
                >
                    Ya, Keluar Akun
                </Button>
            </template>
        </SpringModal>
    </div>
</template>

