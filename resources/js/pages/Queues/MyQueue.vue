<script setup>
import { Link, Head } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Ticket,
    Plus,
    Clock,
    Calendar,
    User,
    ArrowRight,
} from 'lucide-vue-next';

const props = defineProps({
    myQueues: Array,
});

const formatSession = (session) => {
    const sessionName = {
        morning: 'Pagi',
        afternoon: 'Sore',
    };
    return sessionName[session] || session;
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    // If it's an ISO string or includes T, extract the date part
    const cleanDate = dateStr.includes('T') ? dateStr.split('T')[0] : dateStr;
    try {
        const [year, month, day] = cleanDate.split('-');
        return `${day}/${month}/${year}`;
    } catch {
        return cleanDate;
    }
};
</script>

<template>
    <Head title="Tiket Antrean Saya" />

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Compact Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Tiket Antrean Saya</h1>
                <p class="text-xs text-slate-500">Daftar riwayat dan status antrean medis yang Anda daftarkan.</p>
            </div>
            <Button size="sm" class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white" as-child>
                <Link href="/queue">
                    <Plus class="size-3.5" />
                    Ambil Tiket Baru
                </Link>
            </Button>
        </div>

        <!-- Clean Compact Ticket Cards -->
        <div v-if="props.myQueues && props.myQueues.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <Card
                v-for="item in props.myQueues"
                :key="item.queue_id"
                class="p-4 border-slate-200/90 hover:border-slate-300 transition-colors bg-white flex flex-col justify-between gap-3"
            >
                <div class="space-y-3">
                    <!-- Ticket Header: Label & Simple Status Dot -->
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 text-xs">
                        <span class="text-slate-400 font-medium">Tiket Antrean</span>

                        <span v-if="item.queue_status === 'completed'" class="inline-flex items-center gap-1.5 font-medium text-emerald-700">
                            <span class="size-1.5 rounded-full bg-emerald-600"></span> Selesai
                        </span>
                        <span v-else-if="item.queue_status === 'checking'" class="inline-flex items-center gap-1.5 font-medium text-amber-700">
                            <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span> Diperiksa
                        </span>
                        <span v-else-if="item.queue_status === 'called'" class="inline-flex items-center gap-1.5 font-medium text-blue-700">
                            <span class="size-1.5 rounded-full bg-blue-500"></span> Dipanggil
                        </span>
                        <span v-else-if="item.queue_status === 'waiting'" class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                            <span class="size-1.5 rounded-full bg-slate-400"></span> Menunggu
                        </span>
                        <span v-else class="inline-flex items-center gap-1.5 font-medium text-rose-600">
                            <span class="size-1.5 rounded-full bg-rose-500"></span> Batal
                        </span>
                    </div>

                    <!-- Compact Clean Ticket Number -->
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold font-mono text-slate-900 tracking-tight">
                            {{ item.queue_number }}
                        </div>
                        <span class="text-[11px] font-mono text-slate-400">
                            {{ formatSession(item.session) }} ({{ item.estimate_time }})
                        </span>
                    </div>

                    <!-- Clean Details List (Clean minimalist rows) -->
                    <div class="space-y-1.5 text-xs text-slate-600 pt-1 border-t border-slate-50">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Nama Pasien</span>
                            <span class="font-medium text-slate-900">{{ item.patient?.patient_name }}</span>
                        </div>
                        <div class="flex items-center justify-between font-mono text-[11px]">
                            <span class="text-slate-400 font-sans">NIK</span>
                            <span class="text-slate-700">{{ item.patient?.nik || '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between font-mono text-[11px]">
                            <span class="text-slate-400 font-sans">Tgl Periksa</span>
                            <span class="text-slate-700">{{ formatDate(item.queue_date || item.patient?.queue_date) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Link -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">Poli Klinik</span>
                    <Link href="/display-queue" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-0.5">
                        Lihat Display TV <ArrowRight class="size-3" />
                    </Link>
                </div>
            </Card>
        </div>

        <!-- Empty State -->
        <Card v-else class="p-8 text-center text-slate-400 text-xs border-slate-200">
            <Ticket class="size-8 mx-auto mb-2 text-slate-300" />
            <p class="font-semibold text-slate-700">Belum ada antrean terdaftar</p>
            <p class="text-slate-400 mt-0.5 mb-4">Anda belum memiliki tiket antrean aktif saat ini.</p>
            <Button size="sm" as-child class="bg-indigo-600 hover:bg-indigo-700 text-white">
                <Link href="/queue" class="gap-1.5">
                    <Plus class="size-3.5" />
                    Ambil Antrean Sekarang
                </Link>
            </Button>
        </Card>
    </div>
</template>
