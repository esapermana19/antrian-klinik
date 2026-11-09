<script setup>
import { onMounted, onUnmounted, ref, computed, watch } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import {
    Clock,
    Maximize,
    Minimize,
    ArrowLeft,
    CheckCircle2,
    Hourglass,
} from 'lucide-vue-next';

defineOptions({
    layout: null, // Fullscreen Standalone Kiosk Mode
});

const props = defineProps({
    currentQueue: Object,
    nextQueues: Array,
});

const activeQueue = ref(props.currentQueue);
const upcomingList = ref(props.nextQueues || []);

// Sinkronkan bila props dari server berubah
watch(() => props.currentQueue, (newVal) => {
    activeQueue.value = newVal;
});
watch(() => props.nextQueues, (newVal) => {
    upcomingList.value = newVal || [];
});

const currentTime = ref('');
const currentDate = ref('');
const isFullscreen = ref(false);

// Format jam real-time
const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
    currentDate.value = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        isFullscreen.value = true;
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen().catch(() => {});
        }
        isFullscreen.value = false;
    }
};

const handleFullscreenChange = () => {
    isFullscreen.value = !!document.fullscreenElement;
};

// Hitung mundur durasi pemeriksaan (Estimasi 15 menit = 900 detik)
const DURATION_SECONDS = 15 * 60; // 900 detik (15 menit)
const remainingSeconds = ref(DURATION_SECONDS);
let countdownInterval = null;

// Fungsi inisialisasi hitung mundur berdasarkan kapan pasien dipanggil/diperiksa (updated_at)
const initCountdown = () => {
    clearInterval(countdownInterval);

    if (!activeQueue.value) {
        remainingSeconds.value = DURATION_SECONDS;
        return;
    }

    const calculateRemaining = () => {
        if (!activeQueue.value?.updated_at) {
            remainingSeconds.value = DURATION_SECONDS;
            return;
        }

        const callTime = new Date(activeQueue.value.updated_at).getTime();
        const now = new Date().getTime();
        const elapsedSeconds = Math.max(0, Math.floor((now - callTime) / 1000));
        const left = DURATION_SECONDS - elapsedSeconds;

        remainingSeconds.value = left > 0 ? left : 0;
    };

    calculateRemaining();
    countdownInterval = setInterval(() => {
        if (remainingSeconds.value > 0) {
            remainingSeconds.value--;
        }
    }, 1000);
};

// Pantau perubahan pasien aktif untuk reset/sync timer
watch(() => activeQueue.value?.queue_id, () => {
    initCountdown();
});

// Format menit & detik (mm:ss)
const formattedCountdown = computed(() => {
    const minutes = Math.floor(remainingSeconds.value / 60);
    const seconds = remainingSeconds.value % 60;
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

// Ambil maksimal 4 antrean berikutnya
const upcomingQueues = computed(() => {
    return (upcomingList.value || []).slice(0, 4);
});

let clockInterval = null;
let pollInterval = null;
let isFetching = false;

// Polling data cepat (tiap 1.5 detik) langsung via Fetch API agar instant tanpa lag router
const fetchLatestData = async () => {
    if (isFetching) return;
    isFetching = true;
    try {
        const response = await fetch('/display-queue/data', {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store',
        });
        if (response.ok) {
            const data = await response.json();
            activeQueue.value = data.currentQueue;
            upcomingList.value = data.nextQueues || [];
        }
    } catch {
        // Abaikan error koneksi sementara saat polling
    } finally {
        isFetching = false;
    }
};

const startPolling = () => {
    pollInterval = setInterval(fetchLatestData, 1500);
};

onMounted(() => {
    updateClock();
    clockInterval = setInterval(updateClock, 1000);
    initCountdown();
    startPolling();
    document.addEventListener('fullscreenchange', handleFullscreenChange);
});

onUnmounted(() => {
    clearInterval(clockInterval);
    clearInterval(pollInterval);
    clearInterval(countdownInterval);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
});
</script>

<template>
    <Head title="Display Antrean Layar TV" />

    <!-- Container Fullscreen Background Putih Bersih (bg-slate-50 / white) -->
    <div class="h-screen w-screen bg-slate-50 text-slate-800 flex flex-col p-4 md:p-5 overflow-hidden font-sans select-none box-border">
        <!-- 1. Header Display TV Putih Bersih -->
        <header class="flex items-center justify-between pb-3 border-b border-slate-200 shrink-0">
            <div class="flex items-center gap-3">
                <Link
                    href="/dashboard"
                    class="h-9 w-9 rounded-lg bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition"
                    title="Kembali ke Dashboard"
                >
                    <ArrowLeft class="size-4" />
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold tracking-tight text-slate-900 leading-tight">
                        ANTRIAN KLINIK
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        Papan Antrean Poliklinik • {{ currentDate }}
                    </p>
                </div>
            </div>

            <!-- Jam Digital & Fullscreen Button -->
            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    @click="toggleFullscreen"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs text-xs font-medium cursor-pointer"
                    title="Toggle Fullscreen"
                >
                    <component :is="isFullscreen ? Minimize : Maximize" class="size-3.5 text-indigo-600" />
                    <span>{{ isFullscreen ? 'Keluar Fullscreen' : 'Layar Penuh TV' }}</span>
                </button>

                <div class="flex items-center gap-2 bg-white px-3.5 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                    <Clock class="size-4 text-indigo-600" />
                    <span class="text-base font-bold font-mono text-slate-900 tracking-tight">
                        {{ currentTime }}
                    </span>
                </div>
            </div>
        </header>

        <!-- 2. Konten Utama Fullscreen Pas Layar (Background Putih) -->
        <main class="flex-1 grid grid-cols-12 gap-4 items-stretch pt-3 min-h-0 overflow-hidden">
            <!-- Sisi Kiri (7 Kolom): Card Ruang Pemeriksaan (Banner Full + Glassmorphism Foreground) -->
            <div class="col-span-12 lg:col-span-7 flex flex-col min-h-0 h-full">
                <div class="flex-1 rounded-2xl overflow-hidden border border-slate-200/90 relative flex flex-col justify-between shadow-xs bg-white">
                    <!-- Banner Full Memenuhi Seluruh Card (Ilustrasi Vector Tajam) -->
                    <div class="absolute inset-0 z-0 overflow-hidden">
                        <img
                            src="/images/clinic-banner.svg"
                            alt="Ruang Pemeriksaan Dokter"
                            class="w-full h-full object-cover clinic-banner-breathing"
                        />
                        <!-- Soft Gradient Halus di atas Banner -->
                        <div class="absolute inset-0 bg-gradient-to-t from-white/95 via-white/50 to-white/70"></div>
                    </div>

                    <!-- Label Status Atas (Di depan banner) -->
                    <div class="relative z-10 flex items-center justify-between p-5 pb-0 shrink-0">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-indigo-600 animate-pulse"></span>
                            Ruang Pemeriksaan Dokter
                        </span>

                        <!-- Badge Status & Waktu Hitung Mundur 15 Menit -->
                        <div class="flex items-center gap-2">
                            <div
                                v-if="activeQueue"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold border backdrop-blur-md transition-colors"
                                :class="remainingSeconds <= 180
                                    ? 'bg-rose-50 text-rose-700 border-rose-300'
                                    : 'bg-indigo-50 text-indigo-700 border-indigo-200'"
                            >
                                <Hourglass class="size-3.5 animate-spin" style="animation-duration: 4s;" />
                                <span>Sisa Estimasi: {{ formattedCountdown }}</span>
                            </div>

                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 backdrop-blur-md">
                                Sedang Diperiksa
                            </span>
                        </div>
                    </div>

                    <!-- Foreground: Card Nomor Antrean Glassmorphism Murni di Depan Banner -->
                    <div v-if="activeQueue" class="relative z-10 my-auto px-6 py-4 flex flex-col items-center text-center">
                        <div class="w-full max-w-lg bg-white/45 backdrop-blur-2xl border border-white/80 rounded-3xl p-7 shadow-[0_20px_50px_rgba(79,70,229,0.12)] ring-1 ring-white/60">
                            <p class="text-xs font-bold uppercase tracking-widest text-indigo-700/80 mb-1">
                                Nomor Antrean Sedang Dilayani
                            </p>
                            <!-- Nomor Antrean Font Monospace Besar -->
                            <div class="text-6xl md:text-7xl font-black font-mono tracking-tight text-indigo-600 my-1 drop-shadow-sm">
                                {{ activeQueue.queue_number }}
                            </div>
                            <!-- Nama Pasien -->
                            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-1 truncate drop-shadow-xs">
                                {{ activeQueue.patient?.patient_name }}
                            </h2>
                            <div class="mt-2 flex items-center justify-center gap-3 text-xs text-slate-600 font-mono">
                                <span>Sesi: <strong class="text-slate-900">{{ activeQueue.session === 'morning' ? 'Pagi' : 'Sore' }}</strong></span>
                                <span>•</span>
                                <span>Estimasi Panggilan: <strong class="text-slate-900">{{ activeQueue.estimate_time || '-' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- State Kosong jika belum ada panggilan aktif -->
                    <div v-else class="relative z-10 my-auto p-6 text-center">
                        <div class="inline-block bg-white/70 backdrop-blur-xl border border-white/80 rounded-3xl p-7 max-w-sm shadow-md">
                            <h3 class="text-lg font-bold text-slate-800">Belum Ada Panggilan Aktif</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Silakan bersiap di ruang tunggu, dokter akan segera memanggil nomor antrean Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Bar di depan banner -->
                    <div class="relative z-10 p-5 pt-0 flex items-center justify-between text-xs text-slate-500 shrink-0">
                        <span>Poli Rawat Jalan</span>
                        <span>Harap bersiap saat nomor antrean Anda tertera di layar</span>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan (5 Kolom): Antrean Selanjutnya (Card Ungu Solid & Paduan Hitam-Putih Rapi) -->
            <div class="col-span-12 lg:col-span-5 flex flex-col min-h-0 h-full">
                <div class="flex-1 bg-white border border-slate-200/90 rounded-2xl p-5 md:p-6 flex flex-col justify-between overflow-hidden shadow-xs">
                    <!-- Sub-Header Kanan -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">
                                Antrean Selanjutnya
                            </h3>
                            <p class="text-xs text-slate-500">Ruang Tunggu Pasien</p>
                        </div>
                        <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                            {{ (props.nextQueues || []).length }} Menunggu
                        </span>
                    </div>

                    <!-- List Card Antrean Selanjutnya (Ungu Solid & Kombinasi Hitam Putih) -->
                    <div v-if="upcomingQueues.length > 0" class="flex-1 flex flex-col justify-center gap-3 py-3 min-h-0">
                        <div
                            v-for="(queue, index) in upcomingQueues"
                            :key="queue.queue_id"
                            class="p-3.5 rounded-xl border flex items-center justify-between transition-all"
                            :class="index === 0
                                ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-600/20'
                                : 'bg-slate-950 border-slate-900 text-white'"
                        >
                            <!-- Kiri: Nomor Antrean Box Solid & Info Pasien Rapi -->
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Box Nomor Antrean: Kontras Ungu Solid / Hitam-Putih -->
                                <div
                                    class="shrink-0 px-3 py-2 rounded-lg font-mono font-bold text-sm tracking-tight flex items-center justify-center"
                                    :class="index === 0
                                        ? 'bg-white text-indigo-950 shadow-xs'
                                        : 'bg-indigo-600 text-white'"
                                >
                                    {{ queue.queue_number }}
                                </div>

                                <!-- Detail Pasien Terstruktur -->
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm leading-snug truncate" :class="index === 0 ? 'text-white' : 'text-slate-100'">
                                        {{ queue.patient?.patient_name }}
                                    </h4>
                                    <p class="text-[11px] font-mono mt-0.5" :class="index === 0 ? 'text-indigo-100' : 'text-slate-400'">
                                        Estimasi: <span class="font-medium" :class="index === 0 ? 'text-white' : 'text-slate-200'">{{ queue.estimate_time }}</span> • Sesi {{ queue.session === 'morning' ? 'Pagi' : 'Sore' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Kanan: Status Bersiap / Urutan -->
                            <div class="shrink-0 pl-3">
                                <span
                                    v-if="index === 0"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-indigo-900 shadow-xs"
                                >
                                    Bersiap
                                </span>
                                <span
                                    v-else
                                    class="text-xs font-medium text-slate-400"
                                >
                                    Antrean ke-{{ index + 1 }}
                                </span>
                            </div>
                        </div>

                        <!-- Slot Placeholder bila antrean kurang dari 4 -->
                        <div
                            v-for="emptySlot in (4 - upcomingQueues.length)"
                            :key="'slot-' + emptySlot"
                            class="p-3.5 rounded-xl border border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-slate-400 text-xs"
                        >
                            Slot antrean berikutnya belum terisi
                        </div>
                    </div>

                    <!-- Jika Tidak Ada Antrean Menunggu -->
                    <div v-else class="flex-1 flex flex-col items-center justify-center text-center p-6 text-slate-400">
                        <CheckCircle2 class="size-8 text-emerald-500 mb-2" />
                        <p class="font-semibold text-sm text-slate-800">Tidak Ada Antrean Menunggu</p>
                        <p class="text-xs text-slate-400 mt-0.5">Semua pasien pada sesi ini telah selesai dipanggil.</p>
                    </div>

                    <!-- Footer Note Kanan -->
                    <div class="pt-3 border-t border-slate-100 text-center text-xs text-slate-400 shrink-0">
                        Harap memperhatikan nomor panggilan pada layar ini
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Animasi halus napas pada latar belakang banner */
@keyframes bannerBreathing {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.03);
    }
}

.clinic-banner-breathing {
    animation: bannerBreathing 10s ease-in-out infinite;
    transform-origin: center center;
}
</style>
