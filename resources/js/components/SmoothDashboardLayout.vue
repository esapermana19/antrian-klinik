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
    Stethoscope,
    FileSpreadsheet,
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
const userName = computed(
    () => (page.props.auth as any)?.user?.name || 'Pengguna',
);
const userEmail = computed(() => (page.props.auth as any)?.user?.email || '');
const clinicSettings = computed(() => (page.props as any)?.clinic || {});

// Jumlah antrian yang belum diproses (waiting, called, checking)
const pendingQueuesCount = computed(() => {
    return Number((page.props as any)?.pendingQueuesCount ?? 0);
});

// Jumlah obat yang stoknya menipis di bawah batas minimum
const lowStockCount = computed(() => {
    return Number((page.props as any)?.lowStockCount ?? 0);
});

// Raw menus utama (Termasuk Pengaturan & Display TV agar menjadi satu kesatuan navigasi utuh)
const rawMenus: MenuItem[] = [
    {
        name: 'Dashboard',
        href: toUrl(dashboard()),
        icon: LayoutDashboard,
        adminOnly: true,
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
                name: 'Data Dokter',
                href: '/doctor',
                icon: Stethoscope,
            },
            {
                name: 'Manajemen User',
                href: '/users',
                icon: User,
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
        name: 'Laporan',
        href: '/reports',
        icon: FileSpreadsheet,
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
        return rawMenus.filter(
            (item) => !(item.name === 'Data Pasien' && !item.isGroup),
        );
    }
    // Untuk role user: sembunyikan Dashboard admin & adminOnly
    return rawMenus.filter(
        (item) => !item.adminOnly && item.name !== 'Dashboard',
    );
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
    router.post(
        logout(),
        {},
        {
            onFinish: () => {
                router.flushAll();
            },
        },
    );
};
</script>

<template>
    <TooltipProvider :delay-duration="150">
        <div
            class="relative flex min-h-screen overflow-x-hidden bg-slate-50 font-sans text-slate-900 antialiased selection:bg-indigo-500 selection:text-white"
            @mousemove="handleMouseMove"
        >
            <!-- 2. Dynamic Interactive Purple Mesh Gradient Spotlight (Mengikuti Kursor Halus Hampir Tak Terlihat) -->
            <div
                class="pointer-events-none fixed inset-0 z-0 transition-opacity duration-500"
                :style="{
                    background: `radial-gradient(750px circle at ${mouseX}% ${mouseY}%, rgba(99, 102, 241, 0.08), rgba(168, 85, 247, 0.04) 40%, transparent 75%)`,
                }"
            />
            <!-- Secondary Ambient Gradient di Sudut Kanan Atas & Kiri Bawah -->
            <div
                class="pointer-events-none fixed top-0 right-0 -z-10 h-96 w-96 rounded-full bg-purple-200/20 blur-3xl"
            />
            <div
                class="pointer-events-none fixed bottom-0 left-20 -z-10 h-96 w-96 rounded-full bg-indigo-200/20 blur-3xl"
            />

            <!-- 1. Minimalist Sidebar: Desktop (Hidden di layar < lg) -->
            <aside
                class="fixed inset-y-0 left-0 z-40 hidden w-16 flex-col justify-between border-r border-slate-900 bg-slate-950 py-5 shadow-xl select-none lg:flex print:hidden"
            >
                <!-- Top: Daftar Menu dengan Floating Active Indicator Kotak Rounded Halus (Mentok Atas) -->
                <div class="relative flex w-full flex-col items-center px-2.5">
                    <!-- Floating Indicator Solid Purple/Indigo yang meluncur naik/turun saat menu berpindah -->
                    <motion.div
                        v-if="activeIndex !== -1"
                        class="pointer-events-none absolute inset-x-2.5 top-0 z-0 h-10 rounded-xl bg-indigo-600 shadow-md shadow-indigo-600/35"
                        :animate="{ y: activeIndex * ITEM_HEIGHT_PLUS_GAP }"
                        :transition="springIndicator"
                    />

                    <!-- Daftar Ikon Navigasi (Z-10 di atas floating indicator) dengan Tooltip Shadcn UI Tunggal -->
                    <nav
                        class="relative z-10 flex w-full flex-col items-center gap-2"
                    >
                        <template v-for="menu in menus" :key="menu.name">
                            <!-- Menu Dropdown Group: Master Data -->
                            <DropdownMenu v-if="menu.isGroup">
                                <DropdownMenuTrigger as-child>
                                    <button
                                        type="button"
                                        class="group relative flex size-10 cursor-pointer items-center justify-center rounded-xl ring-0 transition-all duration-200 outline-none select-none focus:outline-none focus-visible:outline-none"
                                        :class="
                                            isGroupActive(menu)
                                                ? 'font-semibold text-white'
                                                : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-100'
                                        "
                                    >
                                        <component
                                            :is="menu.icon"
                                            class="pointer-events-none size-4.5 transition-transform duration-200 group-hover:scale-110"
                                            :class="
                                                isGroupActive(menu)
                                                    ? 'stroke-[2.25]'
                                                    : 'stroke-2'
                                            "
                                        />
                                        <!-- Red Dot / Badge Notifikasi Stok Obat Menipis di Group Master Data -->
                                        <span
                                            v-if="
                                                menu.name === 'Master Data' &&
                                                lowStockCount > 0
                                            "
                                            class="pointer-events-none absolute -top-1 -right-1 z-20 flex h-[17px] min-w-[17px] items-center justify-center rounded-full border-2 border-slate-950 bg-rose-600 px-1 font-mono text-[9px] font-bold text-white shadow-md"
                                        >
                                            {{
                                                lowStockCount > 99
                                                    ? '99+'
                                                    : lowStockCount
                                            }}
                                        </span>
                                        <!-- Titik kecil penanda bahwa ini menu kelompok / sub-menu -->
                                        <span
                                            class="absolute right-1 bottom-1 size-1 rounded-full bg-slate-400 group-hover:bg-white"
                                        />
                                    </button>
                                </DropdownMenuTrigger>

                                <DropdownMenuContent
                                    side="right"
                                    :side-offset="14"
                                    align="start"
                                    class="z-50 w-48 rounded-xl border-slate-800 bg-slate-950/95 p-1 text-slate-200 shadow-2xl backdrop-blur-xl"
                                >
                                    <DropdownMenuLabel
                                        class="px-2.5 py-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                    >
                                        {{ menu.name }}
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator
                                        class="my-1 bg-slate-800"
                                    />
                                    <DropdownMenuItem
                                        v-for="sub in menu.children"
                                        :key="sub.name"
                                        as-child
                                    >
                                        <Link
                                            :href="sub.href"
                                            class="flex cursor-pointer items-center justify-between rounded-lg px-2.5 py-1.5 text-xs transition"
                                            :class="
                                                isUrlActive(sub.href)
                                                    ? 'bg-indigo-600 font-medium text-white'
                                                    : 'text-slate-300 hover:bg-slate-900 hover:text-white'
                                            "
                                        >
                                            <div
                                                class="flex items-center gap-2.5"
                                            >
                                                <component
                                                    :is="sub.icon"
                                                    class="size-3.5 stroke-2"
                                                />
                                                <span>{{ sub.name }}</span>
                                            </div>
                                            <span
                                                v-if="
                                                    sub.name === 'Data Obat' &&
                                                    lowStockCount > 0
                                                "
                                                class="rounded-full border border-rose-500/30 bg-rose-500/20 px-1.5 py-0.5 font-mono text-[10px] font-bold text-rose-300"
                                            >
                                                {{ lowStockCount }}
                                            </span>
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
                                        class="group relative flex size-10 cursor-pointer items-center justify-center rounded-xl text-slate-400 ring-0 transition-all duration-200 outline-none select-none hover:bg-slate-900/60 hover:text-slate-100 focus:outline-none focus-visible:outline-none"
                                    >
                                        <component
                                            :is="menu.icon"
                                            class="pointer-events-none size-4.5 stroke-2 transition-transform duration-200 group-hover:scale-110"
                                        />
                                    </a>
                                    <Link
                                        v-else
                                        :href="menu.href!"
                                        tabindex="-1"
                                        class="group relative flex size-10 cursor-pointer items-center justify-center rounded-xl ring-0 transition-all duration-200 outline-none select-none focus:outline-none focus-visible:outline-none"
                                        :class="
                                            isUrlActive(menu.href)
                                                ? 'font-semibold text-white'
                                                : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-100'
                                        "
                                    >
                                        <component
                                            :is="menu.icon"
                                            class="pointer-events-none size-4.5 transition-transform duration-200 group-hover:scale-110"
                                            :class="
                                                isUrlActive(menu.href)
                                                    ? 'stroke-[2.25]'
                                                    : 'stroke-2'
                                            "
                                        />

                                        <!-- Red Badge Notifikasi Antrean Belum Diproses -->
                                        <span
                                            v-if="
                                                menu.name ===
                                                    'Kelola Antrian' &&
                                                pendingQueuesCount > 0
                                            "
                                            class="pointer-events-none absolute -top-1 -right-1 z-20 flex h-[17px] min-w-[17px] animate-pulse items-center justify-center rounded-full border-2 border-slate-950 bg-rose-600 px-1 font-mono text-[9px] font-bold text-white shadow-md"
                                        >
                                            {{
                                                pendingQueuesCount > 99
                                                    ? '99+'
                                                    : pendingQueuesCount
                                            }}
                                        </span>
                                    </Link>
                                </TooltipTrigger>
                                <TooltipContent
                                    side="right"
                                    :side-offset="12"
                                    class="pointer-events-none rounded-lg border border-slate-800 bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-xl select-none"
                                >
                                    {{ menu.name }}
                                </TooltipContent>
                            </Tooltip>
                        </template>
                    </nav>
                </div>

                <!-- Bottom: Logout Action Icon dengan Tooltip Shadcn UI Tunggal -->
                <div
                    class="flex w-full flex-col items-center border-t border-slate-900 px-2.5 pt-3"
                >
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button
                                type="button"
                                @click="showLogoutModal = true"
                                class="group relative flex size-10 cursor-pointer items-center justify-center rounded-xl text-slate-400 transition outline-none hover:bg-rose-500/15 hover:text-rose-400"
                            >
                                <LogOut class="size-4.5 stroke-2" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent
                            side="right"
                            :side-offset="12"
                            class="rounded-lg border border-rose-900/60 bg-rose-950 px-2.5 py-1 text-xs font-medium text-rose-200 shadow-xl"
                        >
                            Keluar Akun
                        </TooltipContent>
                    </Tooltip>
                </div>
            </aside>

            <!-- Main Content Canvas: Offset pl-0 di Mobile, pl-16 di Desktop (lg:pl-16) -->
            <div
                class="relative z-10 flex min-w-0 flex-1 flex-col pl-0 transition-all duration-200 lg:pl-16 print:m-0 print:p-0 print:pl-0"
            >
                <!-- Topbar Header Fixed Glassmorphism macOS Ala Apple -->
                <header
                    class="fixed top-0 right-0 left-0 z-30 flex h-14 items-center justify-between border-b border-slate-200/60 bg-white/80 px-4 shadow-2xs backdrop-blur-xl sm:h-16 sm:px-8 lg:left-16 print:hidden"
                >
                    <!-- Sisi Kiri: Branding Judul Sistem & Logo Klinik -->
                    <div class="flex items-center gap-3">
                        <img
                            v-if="clinicSettings.clinic_logo_url"
                            :src="clinicSettings.clinic_logo_url"
                            alt="Logo Klinik"
                            class="h-7 w-auto max-w-[120px] shrink-0 object-contain"
                        />
                        <span
                            class="text-xs font-bold tracking-wider text-slate-900 uppercase"
                        >
                            {{
                                clinicSettings.clinic_name ||
                                'Sistem Antrean Klinik'
                            }}
                        </span>
                    </div>

                    <!-- Sisi Kanan: User Profile Button with Shadcn Dropdown Menu -->
                    <div class="flex items-center gap-3">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="flex cursor-pointer items-center gap-2.5 rounded-full border border-slate-200/80 bg-white/80 py-1.5 pr-2 pl-3 shadow-2xs backdrop-blur-md transition outline-none hover:bg-white focus-visible:ring-2 focus-visible:ring-indigo-500/30"
                                >
                                    <span
                                        class="hidden text-xs text-slate-500 sm:inline"
                                        >Selamat Datang,</span
                                    >
                                    <span
                                        class="hidden text-xs font-semibold text-slate-900 sm:inline"
                                        >{{ userName }}</span
                                    >
                                    <div
                                        class="flex size-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white shadow-xs"
                                    >
                                        {{ userName.charAt(0).toUpperCase() }}
                                    </div>
                                    <ChevronDown
                                        class="size-3.5 stroke-[2.2] text-slate-400"
                                    />
                                </button>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent
                                class="w-56 rounded-xl border-slate-200 bg-white/95 p-1.5 shadow-xl backdrop-blur-xl"
                                align="end"
                            >
                                <DropdownMenuLabel class="px-2.5 py-2">
                                    <p
                                        class="text-xs leading-tight font-bold text-slate-900"
                                    >
                                        {{ userName }}
                                    </p>
                                    <p
                                        class="mt-0.5 truncate text-[11px] leading-tight font-normal text-slate-400"
                                    >
                                        {{ userEmail }}
                                    </p>
                                    <span
                                        class="mt-1.5 inline-block rounded bg-indigo-50 px-1.5 py-0.5 text-[10px] font-medium text-indigo-700 capitalize"
                                    >
                                        {{ userRole }}
                                    </span>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator
                                    class="my-1 bg-slate-100"
                                />
                                <DropdownMenuItem as-child>
                                    <Link
                                        href="/settings/profile"
                                        class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-2 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600"
                                    >
                                        <User class="size-3.5" />
                                        <span>Profil Saya</span>
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <Link
                                        href="/settings/security"
                                        class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-2 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600"
                                    >
                                        <Shield class="size-3.5" />
                                        <span>Keamanan & Password</span>
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="userRole === 'administrator'"
                                    as-child
                                >
                                    <Link
                                        href="/settings/clinic"
                                        class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-2 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600"
                                    >
                                        <Settings class="size-3.5" />
                                        <span>Pengaturan Klinik</span>
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator
                                    class="my-1 bg-slate-100"
                                />
                                <DropdownMenuItem
                                    @click="showLogoutModal = true"
                                    class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-2 text-xs text-rose-600 hover:bg-rose-50"
                                >
                                    <LogOut class="size-3.5" />
                                    <span>Keluar Akun</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </header>

                <!-- Main Page Slot: Motion Slide-Up Animation untuk Semua Halaman (Offset pt-14/pt-16 agar tidak tertutup header, pb-24 di mobile agar tidak tertutup floating nav) -->
                <!-- Key diikat ke pathname saja agar filter/search/pagination (query string) tidak mentrigger ulang animasi slide-up -->
                <main
                    class="flex-1 pt-14 pb-24 sm:pt-16 lg:pb-10 print:m-0 print:p-0"
                >
                    <motion.div
                        :key="page.url.split(/[?#]/)[0]"
                        :initial="{ opacity: 0, y: 16 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{
                            type: 'spring',
                            stiffness: 320,
                            damping: 28,
                        }"
                    >
                        <slot />
                    </motion.div>
                </main>
            </div>

            <!-- 3. Modern Floating Navigation Bar (Khusus Layar Mobile / Tablet < lg) -->
            <div
                class="pointer-events-none fixed inset-x-0 bottom-4 z-40 flex justify-center px-4 lg:hidden print:hidden"
            >
                <nav
                    class="pointer-events-auto flex items-center gap-1.5 rounded-full border border-slate-800/80 bg-slate-950/90 px-3 py-2 text-slate-400 shadow-2xl ring-1 ring-white/10 backdrop-blur-xl"
                >
                    <template v-for="menu in menus" :key="menu.name">
                        <!-- Group Menu Dropdown di Mobile Floating Nav -->
                        <DropdownMenu v-if="menu.isGroup">
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="relative flex size-9 cursor-pointer items-center justify-center rounded-full transition outline-none"
                                    :class="
                                        isGroupActive(menu)
                                            ? 'bg-indigo-600 font-semibold text-white shadow-xs'
                                            : 'hover:bg-slate-900/60 hover:text-white'
                                    "
                                    :title="menu.name"
                                >
                                    <component
                                        :is="menu.icon"
                                        class="size-4 stroke-2"
                                    />
                                    <!-- Badge mobile jika ada stok obat menipis -->
                                    <span
                                        v-if="
                                            menu.name === 'Master Data' &&
                                            lowStockCount > 0
                                        "
                                        class="pointer-events-none absolute -top-1 -right-1 flex h-[15px] min-w-[15px] items-center justify-center rounded-full border border-slate-950 bg-rose-600 px-0.5 font-mono text-[8px] font-bold text-white"
                                    >
                                        {{ lowStockCount }}
                                    </span>
                                    <span
                                        class="absolute right-1 bottom-1 size-1 rounded-full bg-slate-400"
                                    />
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                side="top"
                                :side-offset="12"
                                align="center"
                                class="z-50 mb-2 w-48 rounded-xl border-slate-800 bg-slate-950/95 p-1 text-slate-200 shadow-2xl backdrop-blur-xl"
                            >
                                <DropdownMenuLabel
                                    class="px-2.5 py-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    {{ menu.name }}
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator
                                    class="my-1 bg-slate-800"
                                />
                                <DropdownMenuItem
                                    v-for="sub in menu.children"
                                    :key="sub.name"
                                    as-child
                                >
                                    <Link
                                        :href="sub.href"
                                        class="flex cursor-pointer items-center justify-between rounded-lg px-2.5 py-2 text-xs transition"
                                        :class="
                                            isUrlActive(sub.href)
                                                ? 'bg-indigo-600 font-medium text-white'
                                                : 'text-slate-300 hover:bg-slate-900 hover:text-white'
                                        "
                                    >
                                        <div class="flex items-center gap-2.5">
                                            <component
                                                :is="sub.icon"
                                                class="size-3.5 stroke-2"
                                            />
                                            <span>{{ sub.name }}</span>
                                        </div>
                                        <span
                                            v-if="
                                                sub.name === 'Data Obat' &&
                                                lowStockCount > 0
                                            "
                                            class="rounded-full border border-rose-500/30 bg-rose-500/20 px-1.5 py-0.5 font-mono text-[10px] font-bold text-rose-300"
                                        >
                                            {{ lowStockCount }}
                                        </span>
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <!-- Single Menu Link di Mobile Floating Nav -->
                        <a
                            v-else-if="menu.openInNewTab"
                            :href="menu.href"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="relative flex size-9 cursor-pointer items-center justify-center rounded-full transition hover:bg-slate-900/60 hover:text-white"
                            :title="menu.name"
                        >
                            <component
                                :is="menu.icon"
                                class="size-4 stroke-2"
                            />
                        </a>
                        <Link
                            v-else
                            :href="menu.href!"
                            class="relative flex size-9 cursor-pointer items-center justify-center rounded-full transition"
                            :class="
                                isUrlActive(menu.href)
                                    ? 'bg-indigo-600 font-semibold text-white shadow-xs'
                                    : 'hover:bg-slate-900/60 hover:text-white'
                            "
                            :title="menu.name"
                        >
                            <component
                                :is="menu.icon"
                                class="size-4 stroke-2"
                            />
                            <span
                                v-if="
                                    menu.name === 'Kelola Antrian' &&
                                    pendingQueuesCount > 0
                                "
                                class="absolute -top-0.5 -right-0.5 flex h-[15px] min-w-[15px] items-center justify-center rounded-full border border-slate-950 bg-rose-600 px-1 font-mono text-[8px] font-bold text-white shadow-md"
                            >
                                {{
                                    pendingQueuesCount > 99
                                        ? '99+'
                                        : pendingQueuesCount
                                }}
                            </span>
                        </Link>
                    </template>
                </nav>
            </div>

            <!-- Spring / Bounce Modal Konfirmasi Logout -->
            <SpringModal
                :open="showLogoutModal"
                @update:open="showLogoutModal = $event"
                title="Konfirmasi Keluar"
                description="Apakah Anda yakin ingin keluar dari sesi sistem antrean klinik ini?"
            >
                <div class="py-1 text-xs leading-relaxed text-slate-600">
                    Seluruh data antrean yang sedang berjalan di sistem tetap
                    aman tersimpan di server.
                </div>

                <template #footer>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="h-8 border-slate-200 text-xs"
                        @click="showLogoutModal = false"
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        class="h-8 bg-rose-600 text-xs text-white hover:bg-rose-700"
                        @click="handleLogout"
                    >
                        Ya, Keluar Akun
                    </Button>
                </template>
            </SpringModal>
        </div>
    </TooltipProvider>
</template>
