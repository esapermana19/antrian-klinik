<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import {
    Activity,
    Clock,
    Users,
    Volume2,
    ArrowLeft,
    CheckCircle2,
} from 'lucide-vue-next';

defineOptions({
    layout: null, // Full-screen kiosk layout without admin sidebar
});

const props = defineProps({
    currentQueue: Object,
    nextQueues: Array,
});

const countdown = ref('');
const currentTime = ref('');

const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

const updateCountdown = () => {
    if (!props.currentQueue?.estimate_time) {
        countdown.value = '-';
        return;
    }

    let targetDate;

    if (
        props.currentQueue.queue_status === 'checking' ||
        props.currentQueue.queue_status === 'called'
    ) {
        targetDate = new Date(props.currentQueue.updated_at);
        targetDate.setMinutes(targetDate.getMinutes() + 15);
    } else {
        const today = new Date().toISOString().split('T')[0];
        targetDate = new Date(`${today}T${props.currentQueue.estimate_time}`);
    }

    const now = new Date();
    const diff = targetDate.getTime() - now.getTime();

    if (diff <= 0) {
        countdown.value = 'Waktu Habis';
        return;
    }

    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    countdown.value = `${minutes}m ${seconds}s`;
};

const formatStatus = (status) => {
    const statusMap = {
        called: 'DIPANGGIL KE RUANG PERIKSA',
        checking: 'SEDANG DIPERIKSA DOKTER',
        completed: 'SELESAI',
    };
    return statusMap[status?.toLowerCase()] || status?.toUpperCase() || '-';
};

let timerInterval;
let reloadInterval;
let clockInterval;

onMounted(() => {
    updateClock();
    updateCountdown();
    clockInterval = setInterval(updateClock, 1000);
    timerInterval = setInterval(updateCountdown, 1000);
    reloadInterval = setInterval(() => {
        router.reload({ only: ['currentQueue', 'nextQueues'] });
    }, 3000);
});

onUnmounted(() => {
    clearInterval(clockInterval);
    clearInterval(timerInterval);
    clearInterval(reloadInterval);
});
</script>

<template>
    <Head title="Display Antrean Pasien" />

    <div class="h-screen w-screen bg-slate-50 flex flex-col p-4 md:p-6 overflow-hidden font-sans text-slate-800 select-none">
        <!-- Topbar Kiosk Header -->
        <header class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <Link
                    href="/dashboard"
                    class="size-9 rounded-lg bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-500 hover:text-slate-900 transition"
                    title="Kembali ke Dashboard"
                >
                    <ArrowLeft class="size-4" />
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                        Papan Antrean Pasien
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700">
                            LIVE
                        </span>
                    </h1>
                    <p class="text-xs text-slate-400">Poliklinik & Pelayanan Rawat Jalan</p>
                </div>
            </div>

            <!-- Jam Digital -->
            <div class="flex items-center gap-2 bg-white px-3.5 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                <Clock class="size-4 text-indigo-600" />
                <span class="text-base md:text-lg font-bold font-mono text-slate-900 tracking-tight">
                    {{ currentTime }}
                </span>
            </div>
        </header>

        <!-- Main Display Content (Fits screen height without scrolling) -->
        <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch pt-4 min-h-0">
            <!-- Left 7 Cols: Antrean Saat Ini (Jumbo Card with clean interactive animation) -->
            <div class="lg:col-span-7 flex flex-col min-h-0">
                <Card class="flex-1 p-6 border-slate-200/90 shadow-xs bg-white rounded-xl flex flex-col justify-between text-center relative overflow-hidden">
                    <!-- Status Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700">
                            <span class="size-2 rounded-full bg-indigo-600 animate-ping"></span>
                            Panggilan Aktif
                        </span>
                        <span class="text-xs text-slate-400 font-mono">
                            Estimasi: <strong class="text-slate-700">{{ countdown }}</strong>
                        </span>
                    </div>

                    <!-- Queue Number and Patient Details -->
                    <div v-if="props.currentQueue" class="my-auto py-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">
                            Nomor Antrean
                        </p>
                        <!-- Animated clean ticket text -->
                        <div class="text-6xl md:text-7xl lg:text-8xl font-black font-mono tracking-tight text-indigo-600 my-2 drop-shadow-2xs animate-pulse">
                            {{ props.currentQueue.queue_number }}
                        </div>
                        <h2 class="text-xl md:text-2xl font-bold text-slate-900 mt-3">
                            {{ props.currentQueue.patient?.patient_name }}
                        </h2>

                        <div class="mt-4">
                            <span
                                class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold"
                                :class="props.currentQueue.queue_status === 'checking'
                                    ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                    : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                            >
                                <span class="size-1.5 rounded-full" :class="props.currentQueue.queue_status === 'checking' ? 'bg-amber-500 animate-ping' : 'bg-emerald-500 animate-ping'"></span>
                                {{ formatStatus(props.currentQueue?.queue_status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Empty State Display -->
                    <div v-else class="my-auto py-12 text-slate-400">
                        <Activity class="size-12 mx-auto mb-3 text-slate-300 stroke-[1.5]" />
                        <h3 class="text-base font-semibold text-slate-700">Belum Ada Panggilan Aktif</h3>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto mt-0.5">Silakan menunggu, nomor antrean berikutnya akan segera dipanggil dokter.</p>
                    </div>

                    <!-- Footer Note -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <span>Ruang Konsultasi Dokter</span>
                        <span>Harap bersiap di ruang tunggu</span>
                    </div>
                </Card>
            </div>

            <!-- Right 5 Cols: Antrean Berikutnya (Clean Compact List) -->
            <div class="lg:col-span-5 flex flex-col min-h-0 space-y-3">
                <div class="flex items-center justify-between pb-1">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                        <Users class="size-4 text-indigo-600" />
                        Antrean Berikutnya
                    </h3>
                    <span class="text-xs text-slate-400 font-medium">Ruang Tunggu</span>
                </div>

                <div v-if="props.nextQueues && props.nextQueues.length > 0" class="flex-1 overflow-y-auto space-y-2 pr-1">
                    <Card
                        v-for="(queue, index) in props.nextQueues"
                        :key="queue.queue_id"
                        class="p-3 border-slate-200 bg-white flex items-center justify-between hover:border-slate-300 transition"
                    >
                        <div class="flex items-center gap-3">
                            <div class="size-9 rounded-md bg-slate-50 border border-slate-200 flex items-center justify-center font-mono font-bold text-indigo-700 text-sm">
                                {{ queue.queue_number }}
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 text-xs">{{ queue.patient?.patient_name }}</h4>
                                <p class="text-[11px] text-slate-400 font-mono">Estimasi: {{ queue.estimate_time }}</p>
                            </div>
                        </div>

                        <span class="text-xs font-mono font-medium text-slate-400">
                            #{{ index + 2 }}
                        </span>
                    </Card>
                </div>

                <Card v-else class="flex-1 p-6 border-slate-200 bg-white rounded-lg flex flex-col items-center justify-center text-center text-slate-400">
                    <CheckCircle2 class="size-8 text-slate-300 mb-1.5" />
                    <p class="font-medium text-xs text-slate-700">Tidak ada antrean lanjutan</p>
                    <p class="text-[11px] text-slate-400">Seluruh antrean telah selesai atau belum ada pendaftaran baru.</p>
                </Card>
            </div>
        </main>
    </div>
</template>
