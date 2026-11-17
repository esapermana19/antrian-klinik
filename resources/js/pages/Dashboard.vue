<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AnimatedCounter from '@/components/AnimatedCounter.vue';
import {
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell,
    TableEmpty,
} from '@/components/ui/table';
import {
    Users,
    Clock,
    UserCheck,
    Ticket,
    Activity,
    Pill,
    AlertCircle,
    Calendar,
    ArrowUpRight,
    ChevronRight,
} from 'lucide-vue-next';

interface Stats {
    totalPatients: number;
    todayQueuesCount: number;
    completedToday: number;
    waitingToday: number;
}

interface QueueItem {
    queue_id: number;
    queue_number: string;
    session: string;
    estimate_time: string;
    queue_status: string;
    patient?: {
        patient_name: string;
        nik: string;
    };
}

interface MedicationItem {
    medication_id: number;
    medication_name: string;
    stock: number;
    min_stock?: number;
}

const props = defineProps<{
    stats?: Stats;
    recentQueues?: QueueItem[];
    lowStockMedications?: MedicationItem[];
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user as any);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const formatSession = (s?: string) => {
    if (s === 'morning') return 'Pagi';
    if (s === 'afternoon') return 'Sore';
    return s || '-';
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'checking':
            return { variant: 'warning' as const, label: 'Diperiksa' };
        case 'called':
            return { variant: 'warning' as const, label: 'Dipanggil' };
        case 'waiting':
            return { variant: 'default' as const, label: 'Menunggu' };
        case 'completed':
            return { variant: 'success' as const, label: 'Selesai' };
        case 'cancel':
            return { variant: 'destructive' as const, label: 'Batal' };
        default:
            return { variant: 'outline' as const, label: status };
    }
};
</script>

<template>
    <Head title="Dashboard Ringkasan" />

    <div class="mx-auto max-w-7xl space-y-3 p-3.5 sm:space-y-4 sm:p-5 md:p-6">
        <!-- Top Bar Compact Header -->
        <div
            class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between sm:gap-3"
        >
            <div>
                <h1
                    class="text-lg leading-tight font-bold tracking-tight text-slate-900 sm:text-xl"
                >
                    {{ greeting }}, {{ user?.name || 'Dokter' }}
                </h1>
                <p class="mt-0.5 text-[11px] text-slate-500 sm:text-xs">
                    Ringkasan operasional antrean pasien dan stok obat hari ini.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    class="h-8 px-2.5 text-xs sm:px-3"
                    as-child
                >
                    <Link href="/display-queue" target="_blank" class="gap-1.5">
                        <Activity class="size-3.5 text-indigo-600" />
                        <span>Layar Antrean</span>
                        <ArrowUpRight class="size-3 text-slate-400" />
                    </Link>
                </Button>
                <Button
                    size="sm"
                    class="h-8 gap-1.5 bg-indigo-600 px-2.5 text-xs text-white shadow-2xs hover:bg-indigo-700 sm:px-3"
                    as-child
                >
                    <Link href="/queue-management">
                        <Ticket class="size-3.5" />
                        <span>Kelola Antrean</span>
                    </Link>
                </Button>
            </div>
        </div>

        <!-- 4 Top Stat Cards (Compact Shadcn Style with Motion Slide-Up & Counting Physics) -->
        <div class="grid grid-cols-2 gap-2.5 sm:gap-3 lg:grid-cols-4">
            <motion.div
                :initial="{ opacity: 0, y: 16 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{
                    type: 'spring',
                    stiffness: 300,
                    damping: 25,
                    delay: 0.05,
                }"
            >
                <Card
                    class="rounded-xl border-slate-200/90 p-3 shadow-2xs transition-shadow hover:shadow-xs sm:p-3.5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-[10px] font-medium text-slate-500 sm:text-[11px]"
                            >
                                Total Pasien
                            </p>
                            <h3
                                class="mt-0.5 text-lg font-bold tracking-tight text-slate-900 sm:text-xl"
                            >
                                <AnimatedCounter
                                    :value="props.stats?.totalPatients ?? 0"
                                />
                            </h3>
                        </div>
                        <div
                            class="flex size-7 items-center justify-center rounded-lg border border-indigo-100/60 bg-indigo-50 text-indigo-600 sm:size-8 sm:rounded-xl"
                        >
                            <Users class="size-3.5 sm:size-4" />
                        </div>
                    </div>
                </Card>
            </motion.div>

            <motion.div
                :initial="{ opacity: 0, y: 16 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{
                    type: 'spring',
                    stiffness: 300,
                    damping: 25,
                    delay: 0.12,
                }"
            >
                <Card
                    class="rounded-xl border-slate-200/90 p-3 shadow-2xs transition-shadow hover:shadow-xs sm:p-3.5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-[10px] font-medium text-slate-500 sm:text-[11px]"
                            >
                                Antrean Hari Ini
                            </p>
                            <h3
                                class="mt-0.5 text-lg font-bold tracking-tight text-slate-900 sm:text-xl"
                            >
                                <AnimatedCounter
                                    :value="props.stats?.todayQueuesCount ?? 0"
                                />
                            </h3>
                        </div>
                        <div
                            class="flex size-7 items-center justify-center rounded-lg border border-blue-100/60 bg-blue-50 text-blue-600 sm:size-8 sm:rounded-xl"
                        >
                            <Ticket class="size-3.5 sm:size-4" />
                        </div>
                    </div>
                </Card>
            </motion.div>

            <motion.div
                :initial="{ opacity: 0, y: 16 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{
                    type: 'spring',
                    stiffness: 300,
                    damping: 25,
                    delay: 0.19,
                }"
            >
                <Card
                    class="rounded-xl border-slate-200/90 p-3 shadow-2xs transition-shadow hover:shadow-xs sm:p-3.5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-[10px] font-medium text-slate-500 sm:text-[11px]"
                            >
                                Menunggu / Cek
                            </p>
                            <h3
                                class="mt-0.5 text-lg font-bold tracking-tight text-amber-600 sm:text-xl"
                            >
                                <AnimatedCounter
                                    :value="props.stats?.waitingToday ?? 0"
                                />
                            </h3>
                        </div>
                        <div
                            class="flex size-7 items-center justify-center rounded-lg border border-amber-100/60 bg-amber-50 text-amber-600 sm:size-8 sm:rounded-xl"
                        >
                            <Clock class="size-3.5 sm:size-4" />
                        </div>
                    </div>
                </Card>
            </motion.div>

            <motion.div
                :initial="{ opacity: 0, y: 16 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{
                    type: 'spring',
                    stiffness: 300,
                    damping: 25,
                    delay: 0.26,
                }"
            >
                <Card
                    class="rounded-xl border-slate-200/90 p-3 shadow-2xs transition-shadow hover:shadow-xs sm:p-3.5"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-[10px] font-medium text-slate-500 sm:text-[11px]"
                            >
                                Selesai Dilayani
                            </p>
                            <h3
                                class="mt-0.5 text-lg font-bold tracking-tight text-emerald-600 sm:text-xl"
                            >
                                <AnimatedCounter
                                    :value="props.stats?.completedToday ?? 0"
                                />
                            </h3>
                        </div>
                        <div
                            class="flex size-7 items-center justify-center rounded-lg border border-emerald-100/60 bg-emerald-50 text-emerald-600 sm:size-8 sm:rounded-xl"
                        >
                            <UserCheck class="size-3.5 sm:size-4" />
                        </div>
                    </div>
                </Card>
            </motion.div>
        </div>

        <!-- Middle Grid: Table Antrean + Info Panel (Slide-Up Staggered Animation) -->
        <motion.div
            class="grid grid-cols-1 gap-4 lg:grid-cols-12"
            :initial="{ opacity: 0, y: 24 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{
                type: 'spring',
                stiffness: 300,
                damping: 25,
                delay: 0.32,
            }"
        >
            <!-- Left 8 Cols: Pasien Antrean Hari Ini (Tabel Rapi Shadcn) -->
            <Card
                class="overflow-hidden rounded-xl border-slate-200/90 p-0 shadow-2xs lg:col-span-8"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 px-4 py-3"
                >
                    <div>
                        <h2
                            class="text-xs font-bold tracking-wider text-slate-900 uppercase"
                        >
                            Antrean Pasien Hari Ini
                        </h2>
                        <p class="text-[11px] text-slate-400">
                            Urutan giliran pemeriksaan klinik saat ini
                        </p>
                    </div>
                    <Link
                        href="/queue-management"
                        class="flex items-center gap-0.5 text-xs font-semibold text-indigo-600 hover:underline"
                    >
                        Lihat Semua <ChevronRight class="size-3" />
                    </Link>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-36 whitespace-nowrap"
                                >No. Tiket</TableHead
                            >
                            <TableHead class="min-w-[180px]"
                                >Nama Pasien</TableHead
                            >
                            <TableHead class="min-w-[140px]"
                                >Sesi & Estimasi</TableHead
                            >
                            <TableHead
                                class="w-28 pr-4 text-right whitespace-nowrap"
                                >Status</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in props.recentQueues"
                            :key="item.queue_id"
                        >
                            <TableCell
                                class="font-mono text-xs font-semibold whitespace-nowrap text-slate-800"
                            >
                                {{ item.queue_number }}
                            </TableCell>
                            <TableCell class="font-medium text-slate-800">
                                <span
                                    class="block text-xs leading-tight text-slate-900"
                                    >{{
                                        item.patient?.patient_name || 'Pasien'
                                    }}</span
                                >
                                <span
                                    class="font-mono text-[11px] font-normal text-slate-400"
                                >
                                    NIK: {{ item.patient?.nik || '-' }}
                                </span>
                            </TableCell>
                            <TableCell class="text-xs text-slate-600">
                                <div>
                                    Sesi {{ formatSession(item.session) }}
                                </div>
                                <div
                                    class="font-mono text-[11px] text-slate-400"
                                >
                                    {{ item.estimate_time }}
                                </div>
                            </TableCell>
                            <TableCell
                                class="pr-4 text-right whitespace-nowrap"
                            >
                                <span
                                    v-if="item.queue_status === 'completed'"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-emerald-600"
                                    ></span>
                                    Selesai
                                </span>
                                <span
                                    v-else-if="item.queue_status === 'checking'"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700"
                                >
                                    <span
                                        class="size-1.5 animate-pulse rounded-full bg-amber-500"
                                    ></span>
                                    Diperiksa
                                </span>
                                <span
                                    v-else-if="item.queue_status === 'called'"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-700"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-blue-500"
                                    ></span>
                                    Dipanggil
                                </span>
                                <span
                                    v-else-if="item.queue_status === 'waiting'"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-slate-400"
                                    ></span>
                                    Menunggu
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-600"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-rose-500"
                                    ></span>
                                    Batal
                                </span>
                            </TableCell>
                        </TableRow>

                        <TableEmpty
                            v-if="
                                !props.recentQueues ||
                                props.recentQueues.length === 0
                            "
                            :colspan="4"
                        >
                            <div
                                class="py-6 text-center text-xs text-slate-400"
                            >
                                Belum ada antrean terdaftar untuk hari ini.
                            </div>
                        </TableEmpty>
                    </TableBody>
                </Table>
            </Card>

            <!-- Right 4 Cols: Panel Ringkas Obat & Jadwal -->
            <div class="space-y-4 lg:col-span-4">
                <!-- Stok Obat Menipis -->
                <Card class="overflow-hidden p-0">
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-4 py-3"
                    >
                        <div class="flex items-center gap-1.5">
                            <AlertCircle class="size-3.5 text-rose-500" />
                            <h3
                                class="text-xs font-bold tracking-wider text-slate-900 uppercase"
                            >
                                Perlu Restock Obat
                            </h3>
                        </div>
                        <Link
                            href="/medication?low_stock=1"
                            class="text-xs font-medium text-indigo-600 hover:underline"
                        >
                            Lihat Semua
                        </Link>
                    </div>

                    <div
                        v-if="
                            props.lowStockMedications &&
                            props.lowStockMedications.length > 0
                        "
                        class="divide-y divide-slate-100"
                    >
                        <div
                            v-for="med in props.lowStockMedications"
                            :key="med.medication_id"
                            class="flex items-center justify-between px-4 py-2 text-xs"
                        >
                            <div class="flex items-center gap-2 truncate">
                                <Pill
                                    class="size-3.5 shrink-0 text-slate-400"
                                />
                                <span
                                    class="truncate font-medium text-slate-700"
                                    >{{ med.medication_name }}</span
                                >
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="font-mono text-[10px] text-slate-400"
                                    >min {{ med.min_stock ?? 10 }}</span
                                >
                                <Badge
                                    variant="destructive"
                                    class="px-1.5 py-0 font-mono text-[10px]"
                                >
                                    {{ med.stock }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="p-4 text-center text-[11px] text-slate-400"
                    >
                        Stok seluruh obat dalam batas aman.
                    </div>
                </Card>

                <!-- Jadwal Hari Ini Box -->
                <Card class="space-y-2.5 p-4">
                    <div class="flex items-center justify-between">
                        <h4
                            class="text-xs font-bold tracking-wider text-slate-900 uppercase"
                        >
                            Jadwal Operasional
                        </h4>
                        <span class="text-[11px] font-medium text-indigo-600"
                            >Buka</span
                        >
                    </div>
                    <p class="text-xs leading-relaxed text-slate-500">
                        Pemeriksaan dokter berlangsung sesuai kuota yang telah
                        dijadwalkan pada sistem.
                    </p>
                    <Button
                        variant="outline"
                        size="sm"
                        class="mt-1 w-full justify-center gap-1.5 text-xs"
                        as-child
                    >
                        <Link href="/practice-schedule">
                            <Calendar class="size-3.5 text-indigo-600" />
                            Lihat Jadwal Praktik
                        </Link>
                    </Button>
                </Card>
            </div>
        </motion.div>
    </div>
</template>
