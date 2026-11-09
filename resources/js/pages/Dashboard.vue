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

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Top Bar Compact Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">
                    {{ greeting }}, {{ user?.name || 'Dokter' }}
                </h1>
                <p class="text-xs text-slate-500">
                    Ringkasan operasional antrean pasien dan stok obat hari ini.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" as-child>
                    <Link href="/display-queue" target="_blank" class="gap-1">
                        <Activity class="size-3.5 text-indigo-600" />
                        Layar Antrean
                        <ArrowUpRight class="size-3 text-slate-400" />
                    </Link>
                </Button>
                <Button size="sm" class="gap-1 bg-indigo-600 hover:bg-indigo-700 text-white" as-child>
                    <Link href="/queue-management">
                        <Ticket class="size-3.5" />
                        Kelola Antrean
                    </Link>
                </Button>
            </div>
        </div>

        <!-- 4 Top Stat Cards (Compact Shadcn Style with Motion Slide-Up & Counting Physics) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.05 }"
            >
                <Card class="p-3.5 rounded-xl border-slate-200/90 shadow-2xs hover:shadow-xs transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-medium text-slate-500">Total Pasien</p>
                            <h3 class="text-xl font-bold text-slate-900 mt-0.5 tracking-tight">
                                <AnimatedCounter :value="props.stats?.totalPatients ?? 0" />
                            </h3>
                        </div>
                        <div class="size-8 rounded-xl bg-indigo-50 border border-indigo-100/60 text-indigo-600 flex items-center justify-center">
                            <Users class="size-4" />
                        </div>
                    </div>
                </Card>
            </motion.div>

            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.12 }"
            >
                <Card class="p-3.5 rounded-xl border-slate-200/90 shadow-2xs hover:shadow-xs transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-medium text-slate-500">Antrean Hari Ini</p>
                            <h3 class="text-xl font-bold text-slate-900 mt-0.5 tracking-tight">
                                <AnimatedCounter :value="props.stats?.todayQueuesCount ?? 0" />
                            </h3>
                        </div>
                        <div class="size-8 rounded-xl bg-blue-50 border border-blue-100/60 text-blue-600 flex items-center justify-center">
                            <Ticket class="size-4" />
                        </div>
                    </div>
                </Card>
            </motion.div>

            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.19 }"
            >
                <Card class="p-3.5 rounded-xl border-slate-200/90 shadow-2xs hover:shadow-xs transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-medium text-slate-500">Menunggu / Cek</p>
                            <h3 class="text-xl font-bold text-amber-600 mt-0.5 tracking-tight">
                                <AnimatedCounter :value="props.stats?.waitingToday ?? 0" />
                            </h3>
                        </div>
                        <div class="size-8 rounded-xl bg-amber-50 border border-amber-100/60 text-amber-600 flex items-center justify-center">
                            <Clock class="size-4" />
                        </div>
                    </div>
                </Card>
            </motion.div>

            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.26 }"
            >
                <Card class="p-3.5 rounded-xl border-slate-200/90 shadow-2xs hover:shadow-xs transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-medium text-slate-500">Selesai Dilayani</p>
                            <h3 class="text-xl font-bold text-emerald-600 mt-0.5 tracking-tight">
                                <AnimatedCounter :value="props.stats?.completedToday ?? 0" />
                            </h3>
                        </div>
                        <div class="size-8 rounded-xl bg-emerald-50 border border-emerald-100/60 text-emerald-600 flex items-center justify-center">
                            <UserCheck class="size-4" />
                        </div>
                    </div>
                </Card>
            </motion.div>
        </div>

        <!-- Middle Grid: Table Antrean + Info Panel (Slide-Up Staggered Animation) -->
        <motion.div
            class="grid grid-cols-1 lg:grid-cols-12 gap-4"
            :initial="{ opacity: 0, y: 24 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ type: 'spring', stiffness: 300, damping: 25, delay: 0.32 }"
        >
            <!-- Left 8 Cols: Pasien Antrean Hari Ini (Tabel Rapi Shadcn) -->
            <Card class="lg:col-span-8 p-0 overflow-hidden rounded-xl border-slate-200/90 shadow-2xs">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Antrean Pasien Hari Ini</h2>
                        <p class="text-[11px] text-slate-400">Urutan giliran pemeriksaan klinik saat ini</p>
                    </div>
                    <Link href="/queue-management" class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-0.5">
                        Lihat Semua <ChevronRight class="size-3" />
                    </Link>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-36 whitespace-nowrap">No. Tiket</TableHead>
                            <TableHead class="min-w-[180px]">Nama Pasien</TableHead>
                            <TableHead class="min-w-[140px]">Sesi & Estimasi</TableHead>
                            <TableHead class="text-right pr-4 w-28 whitespace-nowrap">Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in props.recentQueues"
                            :key="item.queue_id"
                        >
                            <TableCell class="font-mono font-semibold text-slate-800 text-xs whitespace-nowrap">
                                {{ item.queue_number }}
                            </TableCell>
                            <TableCell class="font-medium text-slate-800">
                                <span class="block leading-tight text-xs text-slate-900">{{ item.patient?.patient_name || 'Pasien' }}</span>
                                <span class="text-[11px] text-slate-400 font-normal font-mono">
                                    NIK: {{ item.patient?.nik || '-' }}
                                </span>
                            </TableCell>
                            <TableCell class="text-xs text-slate-600">
                                <div>Sesi {{ formatSession(item.session) }}</div>
                                <div class="text-slate-400 font-mono text-[11px]">
                                    {{ item.estimate_time }}
                                </div>
                            </TableCell>
                            <TableCell class="text-right pr-4 whitespace-nowrap">
                                <span v-if="item.queue_status === 'completed'" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                    <span class="size-1.5 rounded-full bg-emerald-600"></span> Selesai
                                </span>
                                <span v-else-if="item.queue_status === 'checking'" class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700">
                                    <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span> Diperiksa
                                </span>
                                <span v-else-if="item.queue_status === 'called'" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-700">
                                    <span class="size-1.5 rounded-full bg-blue-500"></span> Dipanggil
                                </span>
                                <span v-else-if="item.queue_status === 'waiting'" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">
                                    <span class="size-1.5 rounded-full bg-slate-400"></span> Menunggu
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                    <span class="size-1.5 rounded-full bg-rose-500"></span> Batal
                                </span>
                            </TableCell>
                        </TableRow>

                        <TableEmpty v-if="!props.recentQueues || props.recentQueues.length === 0" :colspan="4">
                            <div class="py-6 text-center text-slate-400 text-xs">
                                Belum ada antrean terdaftar untuk hari ini.
                            </div>
                        </TableEmpty>
                    </TableBody>
                </Table>
            </Card>

            <!-- Right 4 Cols: Panel Ringkas Obat & Jadwal -->
            <div class="lg:col-span-4 space-y-4">
                <!-- Stok Obat Menipis -->
                <Card class="p-0 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <AlertCircle class="size-3.5 text-rose-500" />
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Perlu Restock Obat</h3>
                        </div>
                        <Link href="/medication" class="text-xs font-medium text-indigo-600 hover:underline">
                            Kelola
                        </Link>
                    </div>

                    <div v-if="props.lowStockMedications && props.lowStockMedications.length > 0" class="divide-y divide-slate-100">
                        <div
                            v-for="med in props.lowStockMedications"
                            :key="med.medication_id"
                            class="flex items-center justify-between px-4 py-2 text-xs"
                        >
                            <div class="flex items-center gap-2 truncate">
                                <Pill class="size-3.5 text-slate-400 shrink-0" />
                                <span class="text-slate-700 font-medium truncate">{{ med.medication_name }}</span>
                            </div>
                            <Badge variant="destructive" class="text-[10px] px-1.5 py-0 font-mono">
                                {{ med.stock }}
                            </Badge>
                        </div>
                    </div>
                    <div v-else class="p-4 text-center text-[11px] text-slate-400">
                        Stok seluruh obat dalam batas aman.
                    </div>
                </Card>

                <!-- Jadwal Hari Ini Box -->
                <Card class="p-4 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Jadwal Operasional</h4>
                        <span class="text-[11px] text-indigo-600 font-medium">Buka</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Pemeriksaan dokter berlangsung sesuai kuota yang telah dijadwalkan pada sistem.
                    </p>
                    <Button variant="outline" size="sm" class="w-full text-xs justify-center gap-1.5 mt-1" as-child>
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
