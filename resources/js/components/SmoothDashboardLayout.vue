<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import {
    LayoutDashboard,
    ListChecks,
    Users,
    Ticket,
    TicketCheck,
    Pill,
    Receipt,
    Layers,
    Calendar,
    LogOut,
    Activity,
    Tv,
    Settings,
    ChevronDown,
    ChevronRight,
    Database,
    User,
    Shield,
    Bell,
    Wallet,
} from 'lucide-vue-next';
import { dashboard, logout } from '@/routes';
import medicationRoute from '@/routes/medication';
import patientRoute from '@/routes/patient';
import scheduleRoute from '@/routes/practice-schedule';
import medicationCategoryRoute from '@/routes/medication-category';
import queueRoute from '@/routes/queue';
import queueManagement from '@/routes/queue-management';
import myQueue from '@/routes/my-queue';
import SpringModal from '@/components/SpringModal.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { toUrl } from '@/lib/utils';

interface MenuItem {
    name: string;
    href?: string;
    icon: any;
    adminOnly?: boolean;
    openInNewTab?: boolean;
    isGroup?: boolean;
    children?: {
        name: string;
        href: string;
        icon: any;
    }[];
}

const page = usePage();
const showLogoutModal = ref(false);

const userRole = computed(() => (page.props.auth as any)?.user?.role);
const userName = computed(() => (page.props.auth as any)?.user?.name || 'Pengguna');
const userEmail = computed(() => (page.props.auth as any)?.user?.email || '');

// Jumlah antrian yang belum diproses (waiting, called, checking)
const pendingQueuesCount = computed(() => {
    return Number((page.props as any)?.pendingQueuesCount ?? 0);
});

// Raw menus utama (Termasuk Pengaturan & Display TV agar menjadi satu kesatuan navigasi utuh)
const rawMenus: MenuItem[] = [
    {
        name: 'Dashboard',
        href: toUrl(dashboard()),
        icon: LayoutDashboard,
    },
    {
        name: 'Master Data',
        icon: Database,
        adminOnly: true,
        isGroup: true,
        children: [
            {
                name: 'Data Pasien',
                href: toUrl(patientRoute.index()),
                icon: Users,
            },
            {
                name: 'Data Obat',
                href: toUrl(medicationRoute.index()),
                icon: Pill,
            },
            {
                name: 'Kategori Obat',
                href: toUrl(medicationCategoryRoute.index()),
                icon: Layers,
            },
            {
                name: 'Jadwal Praktik',
                href: toUrl(scheduleRoute.index()),
                icon: Calendar,
            },
        ],
    },
    {
        name: 'Kelola Antrian',
        href: toUrl(queueManagement.index()),
        icon: ListChecks,
        adminOnly: true,
    },
    {
        name: 'Layanan Antrean',
        icon: Ticket,
        adminOnly: false,
        isGroup: true,
        children: [
            {
                name: 'Ambil Antrian',
                href: toUrl(queueRoute.index()),
                icon: Ticket,
            },
            {
                name: 'Antrian Saya',
                href: toUrl(myQueue.index()),
                icon: TicketCheck,
            },
        ],
    },
    {
        name: 'Kasir & Tagihan',
        href: '/invoices',
        icon: Wallet,
        adminOnly: true,
    },
    {
        name: 'Data Pasien',
        href: toUrl(patientRoute.index()),
        icon: Users,
        adminOnly: false, // Ditampilkan hanya untuk non-admin
    },
    {
        name: 'Layar TV Antrean',
        href: '/display-queue',
        icon: Tv,
        adminOnly: false,
        openInNewTab: true,
    },
    {
        name: 'Pengaturan Akun',
        href: '/settings/profile',
        icon: Settings,
        adminOnly: false,
    },
];

const menus = computed(() => {
    if (userRole.value === 'administrator') {
        // Untuk admin: sembunyikan item pasien non-admin tunggal
        return rawMenus.filter((item) => !(item.name === 'Data Pasien' && !item.isGroup));
    }
    // Untuk non-admin: sembunyikan adminOnly (tetap sertakan Layanan Antrean isGroup)
    return rawMenus.filter((item) => !item.adminOnly);
});

// Helper URL match (menangani exact match maupun sub-path)
const isUrlActive = (href?: string) => {
    if (!href) return false;
    const currentPath = page.url.split('?')[0].replace(/\/+$/, '');
    const targetPath = href.split('?')[0].replace(/\/+$/, '');

    if (targetPath === '/settings/profile') {
        return currentPath.startsWith('/settings');
    }
    if (targetPath === '' || targetPath === '/dashboard') {
        return currentPath === '' || currentPath === '/dashboard';
    }
    // Cocokkan persis atau pastikan boundary URL (misal: /medication-category bukan bagian dari /medication)
    if (currentPath === targetPath) {
        return true;
    }
    return currentPath.startsWith(`${targetPath}/`);
};

// Cek apakah salah satu anak dari sub-menu group sedang aktif
const isGroupActive = (item: MenuItem) => {
    if (!item.children) return false;
    return item.children.some((child) => isUrlActive(child.href));
};

// Computed activeIndex untuk mengarahkan floating indicator
const activeIndex = computed(() => {
    return menus.value.findIndex((menu) => {
        if (menu.isGroup) {
            return isGroupActive(menu);
        }
        return isUrlActive(menu.href);
    });
});

// Dimensi matematika layout menu:
// Item size: 40px (size-10), Gap antar item: 8px (gap-2)
// Total langkah Y = 40 + 8 = 48px
const ITEM_HEIGHT_PLUS_GAP = 48;

// Kurva physics spring untuk efek meluncur membal lembut
const springIndicator = {
    type: 'spring',
    stiffness: 350,
    damping: 26,
    mass: 0.8,
};

// Interactive Mouse Gradient Spotlight (Subtle Purple Mesh Following Cursor)
const mouseX = ref(50);
const mouseY = ref(50);

const handleMouseMove = (e: MouseEvent) => {
    // Menghitung persentase posisi mouse relatif terhadap window
    const x = Math.round((e.clientX / window.innerWidth) * 100);
    const y = Math.round((e.clientY / window.innerHeight) * 100);
    mouseX.value = x;
    mouseY.value = y;
};

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
    <TooltipProvider :delay-duration="150">
        <div
            class="min-h-screen bg-slate-50 text-slate-900 flex font-sans antialiased relative overflow-x-hidden selection:bg-indigo-500 selection:text-white"
            @mousemove="handleMouseMove"
        >
            <!-- 2. Dynamic Interactive Purple Mesh Gradient Spotlight (Mengikuti Kursor Halus Hampir Tak Terlihat) -->
            <div
                class="fixed inset-0 pointer-events-none z-0 transition-opacity duration-500"
                :style="{
                    background: `radial-gradient(750px circle at ${mouseX}% ${mouseY}%, rgba(99, 102, 241, 0.08), rgba(168, 85, 247, 0.04) 40%, transparent 75%)`,
                }"
            />
            <!-- Secondary Ambient Gradient di Sudut Kanan Atas & Kiri Bawah -->
            <div class="fixed top-0 right-0 -z-10 w-96 h-96 bg-purple-200/20 rounded-full blur-3xl pointer-events-none" />
            <div class="fixed bottom-0 left-20 -z-10 w-96 h-96 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none" />

            <!-- 1. Minimalist Sidebar: Lebar Ramping w-16, Hitam Pekat bg-slate-950, Compact & Kelengkungan Sesuai rounded-xl -->
            <aside class="fixed inset-y-0 left-0 z-40 flex flex-col justify-between w-16 bg-slate-950 border-r border-slate-900 py-5 select-none shadow-xl">
                <!-- Top: Daftar Menu dengan Floating Active Indicator Kotak Rounded Halus (Mentok Atas) -->
                <div class="relative flex flex-col items-center w-full px-2.5">
                    <!-- Floating Indicator Solid Purple/Indigo yang meluncur naik/turun saat menu berpindah -->
                    <motion.div
                        v-if="activeIndex !== -1"
                        class="absolute inset-x-2.5 top-0 h-10 rounded-xl bg-indigo-600 shadow-md shadow-indigo-600/35 pointer-events-none z-0"
                        :animate="{ y: activeIndex * ITEM_HEIGHT_PLUS_GAP }"
                        :transition="springIndicator"
                    />

                    <!-- Daftar Ikon Navigasi (Z-10 di atas floating indicator) dengan Tooltip Shadcn UI Tunggal -->
                    <nav class="relative z-10 flex flex-col items-center gap-2 w-full">
                        <template v-for="menu in menus" :key="menu.name">
                            <!-- Menu Dropdown Group: Master Data -->
                            <DropdownMenu v-if="menu.isGroup">
                                <DropdownMenuTrigger as-child>
                                    <button
                                        type="button"
                                        class="group relative flex items-center justify-center size-10 rounded-xl transition-all duration-200 cursor-pointer outline-none select-none focus:outline-none focus-visible:outline-none ring-0"
                                        :class="
                                            isGroupActive(menu)
                                                ? 'text-white font-semibold'
                                                : 'text-slate-400 hover:text-slate-100 hover:bg-slate-900/60'
                                        "
                                    >
                                        <component
                                            :is="menu.icon"
                                            class="size-4.5 transition-transform duration-200 group-hover:scale-110 pointer-events-none"
                                            :class="isGroupActive(menu) ? 'stroke-[2.25]' : 'stroke-2'"
                                        />
                                        <!-- Titik kecil penanda bahwa ini menu kelompok / sub-menu -->
                                        <span class="absolute bottom-1 right-1 size-1 rounded-full bg-slate-400 group-hover:bg-white" />
                                    </button>
                                </DropdownMenuTrigger>

                                <DropdownMenuContent
                                    side="right"
                                    :side-offset="14"
                                    align="start"
                                    class="w-48 bg-slate-950/95 backdrop-blur-xl border-slate-800 shadow-2xl p-1 rounded-xl text-slate-200 z-50"
                                >
                                    <DropdownMenuLabel class="px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        {{ menu.name }}
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator class="my-1 bg-slate-800" />
                                    <DropdownMenuItem
                                        v-for="sub in menu.children"
                                        :key="sub.name"
                                        as-child
                                    >
                                        <Link
                                            :href="sub.href"
                                            class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs rounded-lg transition cursor-pointer"
                                            :class="
                                                isUrlActive(sub.href)
                                                    ? 'bg-indigo-600 text-white font-medium'
                                                    : 'text-slate-300 hover:bg-slate-900 hover:text-white'
                                            "
                                        >
                                            <component :is="sub.icon" class="size-3.5 stroke-2" />
                                            <span>{{ sub.name }}</span>
                                        </Link>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <!-- Single Menu Link Biasa -->
                            <Tooltip v-else>
                                <TooltipTrigger as-child>
                                    <a
                                        v-if="menu.openInNewTab"
                                        :href="menu.href"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        tabindex="-1"
                                        class="group relative flex items-center justify-center size-10 rounded-xl transition-all duration-200 cursor-pointer outline-none select-none focus:outline-none focus-visible:outline-none ring-0 text-slate-400 hover:text-slate-100 hover:bg-slate-900/60"
                                    >
                                        <component
                                            :is="menu.icon"
                                            class="size-4.5 transition-transform duration-200 group-hover:scale-110 pointer-events-none stroke-2"
                                        />
                                    </a>
                                    <Link
                                        v-else
                                        :href="menu.href!"
                                        tabindex="-1"
                                        class="group relative flex items-center justify-center size-10 rounded-xl transition-all duration-200 cursor-pointer outline-none select-none focus:outline-none focus-visible:outline-none ring-0"
                                        :class="
                                            isUrlActive(menu.href)
                                                ? 'text-white font-semibold'
                                                : 'text-slate-400 hover:text-slate-100 hover:bg-slate-900/60'
                                        "
                                    >
                                        <component
                                            :is="menu.icon"
                                            class="size-4.5 transition-transform duration-200 group-hover:scale-110 pointer-events-none"
                                            :class="isUrlActive(menu.href) ? 'stroke-[2.25]' : 'stroke-2'"
                                        />

                                        <!-- Red Badge Notifikasi Antrean Belum Diproses -->
                                        <span
                                            v-if="menu.name === 'Kelola Antrian' && pendingQueuesCount > 0"
                                            class="absolute -top-1 -right-1 flex items-center justify-center min-w-[17px] h-[17px] px-1 rounded-full bg-rose-600 text-white text-[9px] font-bold font-mono shadow-md border-2 border-slate-950 animate-pulse pointer-events-none z-20"
                                        >
                                            {{ pendingQueuesCount > 99 ? '99+' : pendingQueuesCount }}
                                        </span>
                                    </Link>
                                </TooltipTrigger>
                                <TooltipContent
                                    side="right"
                                    :side-offset="12"
                                    class="bg-slate-900 text-white border border-slate-800 text-xs font-medium py-1 px-2.5 shadow-xl rounded-lg pointer-events-none select-none"
                                >
                                    {{ menu.name }}
                                </TooltipContent>
                            </Tooltip>
                        </template>
                    </nav>
                </div>

                <!-- Bottom: Logout Action Icon dengan Tooltip Shadcn UI Tunggal -->
                <div class="flex flex-col items-center pt-3 border-t border-slate-900 w-full px-2.5">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button
                                type="button"
                                @click="showLogoutModal = true"
                                class="group relative flex items-center justify-center size-10 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/15 transition cursor-pointer outline-none"
                            >
                                <LogOut class="size-4.5 stroke-2" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent
                            side="right"
                            :side-offset="12"
                            class="bg-rose-950 text-rose-200 border border-rose-900/60 text-xs font-medium py-1 px-2.5 shadow-xl rounded-lg"
                        >
                            Keluar Akun
                        </TooltipContent>
                    </Tooltip>
                </div>
            </aside>

            <!-- Main Content Canvas: Offset pl-16 agar proporsional dengan sidebar w-16 -->
            <div class="flex-1 flex flex-col min-w-0 pl-16 transition-all duration-200 relative z-10">
                <!-- Topbar Header Fixed Glassmorphism macOS Ala Apple -->
                <header class="fixed top-0 right-0 left-16 z-30 flex h-16 items-center justify-between border-b border-white/60 bg-white/70 backdrop-blur-xl px-8 shadow-2xs">
                    <!-- Sisi Kiri: Branding Judul Sistem -->
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-900">
                            Sistem Antrean Klinik
                        </span>
                    </div>

                    <!-- Sisi Kanan: User Profile Button with Shadcn Dropdown Menu -->
                    <div class="flex items-center gap-3">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="flex items-center gap-2.5 pl-3 pr-2 py-1.5 rounded-full border border-slate-200/80 bg-white/80 backdrop-blur-md hover:bg-white transition cursor-pointer outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/30 shadow-2xs"
                                >
                                    <span class="text-xs text-slate-500 hidden sm:inline">Selamat Datang,</span>
                                    <span class="text-xs font-semibold text-slate-900 hidden sm:inline">{{ userName }}</span>
                                    <div class="size-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                        {{ userName.charAt(0).toUpperCase() }}
                                    </div>
                                    <ChevronDown class="size-3.5 text-slate-400 stroke-[2.2]" />
                                </button>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent class="w-56 bg-white/95 backdrop-blur-xl border-slate-200 shadow-xl p-1.5 rounded-xl" align="end">
                                <DropdownMenuLabel class="px-2.5 py-2">
                                    <p class="text-xs font-bold text-slate-900 leading-tight">{{ userName }}</p>
                                    <p class="text-[11px] text-slate-400 font-normal leading-tight mt-0.5 truncate">{{ userEmail }}</p>
                                    <span class="inline-block mt-1.5 text-[10px] font-medium px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 capitalize">
                                        {{ userRole }}
                                    </span>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator class="my-1 bg-slate-100" />
                                <DropdownMenuItem as-child>
                                    <Link
                                        href="/settings/profile"
                                        class="flex items-center gap-2 px-2.5 py-2 text-xs text-slate-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 cursor-pointer"
                                    >
                                        <User class="size-3.5" />
                                        <span>Profil Saya</span>
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <Link
                                        href="/settings/security"
                                        class="flex items-center gap-2 px-2.5 py-2 text-xs text-slate-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 cursor-pointer"
                                    >
                                        <Shield class="size-3.5" />
                                        <span>Keamanan & Password</span>
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="my-1 bg-slate-100" />
                                <DropdownMenuItem
                                    @click="showLogoutModal = true"
                                    class="flex items-center gap-2 px-2.5 py-2 text-xs text-rose-600 rounded-lg hover:bg-rose-50 cursor-pointer"
                                >
                                    <LogOut class="size-3.5" />
                                    <span>Keluar Akun</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </header>

                <!-- Main Page Slot: Motion Slide-Up Animation untuk Semua Halaman (Offset pt-16 agar tidak tertutup header fixed) -->
                <!-- Key diikat ke pathname saja agar filter/search/pagination (query string) tidak mentrigger ulang animasi slide-up -->
                <main class="flex-1 pt-16 pb-10">
                    <motion.div
                        :key="page.url.split(/[?#]/)[0]"
                        :initial="{ opacity: 0, y: 16 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ type: 'spring', stiffness: 320, damping: 28 }"
                    >
                        <slot />
                    </motion.div>
                </main>
            </div>

            <!-- Spring / Bounce Modal Konfirmasi Logout -->
            <SpringModal
                :open="showLogoutModal"
                @update:open="showLogoutModal = $event"
                title="Konfirmasi Keluar"
                description="Apakah Anda yakin ingin keluar dari sesi sistem antrean klinik ini?"
            >
                <div class="text-xs text-slate-600 leading-relaxed py-1">
                    Seluruh data antrean yang sedang berjalan di sistem tetap aman tersimpan di server.
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
    </TooltipProvider>
</template>
