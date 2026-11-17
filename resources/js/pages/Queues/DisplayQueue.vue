<script setup>
import { onMounted, onUnmounted, ref, computed, watch } from 'vue';
import { router, Head, Link, usePage } from '@inertiajs/vue3';
import {
    Clock,
    Maximize,
    Minimize,
    ArrowLeft,
    CheckCircle2,
    Hourglass,
    Volume2,
    VolumeX,
} from 'lucide-vue-next';
import { useQueueAnnouncer } from '@/composables/useQueueAnnouncer';

defineOptions({
    layout: null, // Fullscreen Standalone Kiosk Mode
});

const props = defineProps({
    currentQueue: Object,
    nextQueues: Array,
    doctors: Array,
    selectedDoctorId: [String, Number],
});

const page = usePage();
const clinicName = computed(() => page.props.clinic?.clinic_name || 'ANTRIAN KLINIK');
const clinicLogoUrl = computed(() => page.props.clinic?.clinic_logo_url);

const selectedDoctor = ref(props.selectedDoctorId || '');
const activeQueue = ref(props.currentQueue);
const upcomingList = ref(props.nextQueues || []);

const { isMuted, toggleMute, announceQueue } = useQueueAnnouncer();
const lastAnnouncedQueueKey = ref('');

// Fungsi memanggil suara jika ada antrean baru yang aktif dipanggil
const checkAndAnnounce = (queue) => {
    if (!queue || !queue.queue_number) return;
    const currentKey = `${queue.queue_id}_${queue.queue_number}_${queue.queue_status}_${queue.updated_at}`;
    if (lastAnnouncedQueueKey.value !== currentKey) {
        lastAnnouncedQueueKey.value = currentKey;
        // Panggil suara
        announceQueue(
            queue.queue_number,
            queue.patient?.patient_name,
            queue.doctor?.doctor_name
        );
    }
};

// Sinkronkan bila props dari server berubah
watch(() => props.currentQueue, (newVal) => {
    activeQueue.value = newVal;
    if (newVal) {
        checkAndAnnounce(newVal);
    }
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
        const url = selectedDoctor.value
            ? `/display-queue/data?doctor_id=${selectedDoctor.value}`
            : '/display-queue/data';
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store',
        });
        if (response.ok) {
            const data = await response.json();
            activeQueue.value = data.currentQueue;
            upcomingList.value = data.nextQueues || [];
            if (data.currentQueue) {
                checkAndAnnounce(data.currentQueue);
            }
        }
    } catch {
        // Abaikan error koneksi sementara saat polling
    } finally {
        isFetching = false;
    }
};

const changeDoctor = (docId) => {
    selectedDoctor.value = docId;
    fetchLatestData();
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
    <Head :title="`Display Antrean - ${clinicName}`" />

    <!-- Container Full Viewport: Pas Layar, Kompak, Tanpa Scrollbar (h-screen w-screen overflow-hidden) -->
    <div class="h-screen w-screen bg-slate-50 text-slate-800 flex flex-col p-3 sm:p-4 md:p-5 overflow-hidden font-sans select-none box-border">
        
        <!-- 1. Header Display TV: Ramping & Kompak (shrink-0) -->
        <header class="flex items-center justify-between gap-3 pb-2.5 sm:pb-3 border-b border-slate-200 shrink-0">
            <!-- Sisi Kiri: Tombol Back & Branding Klinik -->
            <div class="flex items-center gap-2.5 min-w-0">
                <Link
                    href="/dashboard"
                    class="size-8 sm:size-9 rounded-lg bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition shrink-0"
                    title="Kembali ke Dashboard"
                >
                    <ArrowLeft class="size-4" />
                </Link>
                <img
                    v-if="clinicLogoUrl"
                    :src="clinicLogoUrl"
                    alt="Logo Klinik"
                    class="h-8 sm:h-9 w-auto max-w-[100px] object-contain shrink-0"
                />
                <div class="min-w-0">
                    <h1 class="text-base sm:text-lg md:text-xl font-bold tracking-tight text-slate-900 leading-tight uppercase truncate">
                        {{ clinicName }}
                    </h1>
                    <p class="text-[10px] sm:text-xs text-slate-500 font-medium truncate">
                        Papan Antrean Poliklinik • {{ currentDate }}
                    </p>
                </div>
            </div>

            <!-- Sisi Kanan: Filter Dokter/Poli, Kontrol Suara, Jam Digital & Fullscreen Button -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- Tombol Suara TTS (Mute / Unmute) -->
                <button
                    type="button"
                    @click="toggleMute"
                    class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg border transition shadow-2xs text-[11px] sm:text-xs font-medium cursor-pointer"
                    :class="isMuted
                        ? 'bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100'
                        : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'"
                    :title="isMuted ? 'Suara Panggilan Dinonaktifkan (Klik untuk Aktifkan)' : 'Suara Panggilan Aktif (Klik untuk Mute)'"
                >
                    <component :is="isMuted ? VolumeX : Volume2" class="size-3.5" :class="isMuted ? 'text-rose-600' : 'text-indigo-600'" />
                    <span class="hidden sm:inline">{{ isMuted ? 'Mute' : 'Suara Aktif' }}</span>
                </button>

                <!-- Dropdown Poli / Dokter -->
                <div v-if="doctors && doctors.length > 0" class="flex items-center bg-white border border-slate-200 rounded-lg px-2 py-1 shadow-2xs">
                    <select
                        v-model="selectedDoctor"
                        @change="changeDoctor(selectedDoctor)"
                        class="text-[11px] sm:text-xs font-medium text-slate-800 bg-transparent border-0 p-0 focus:outline-none cursor-pointer"
                    >
                        <option value="">Semua Poli / Dokter</option>
                        <option v-for="doc in doctors" :key="doc.doctor_id" :value="doc.doctor_id">
                            {{ doc.doctor_name }} ({{ doc.specialization }})
                        </option>
                    </select>
                </div>

                <button
                    type="button"
                    @click="toggleFullscreen"
                    class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs text-[11px] sm:text-xs font-medium cursor-pointer"
                    title="Toggle Fullscreen"
                >
                    <component :is="isFullscreen ? Minimize : Maximize" class="size-3.5 text-indigo-600" />
                    <span class="hidden sm:inline">{{ isFullscreen ? 'Keluar' : 'Layar Penuh TV' }}</span>
                </button>

                <div class="flex items-center gap-1.5 sm:gap-2 bg-white px-2.5 sm:px-3 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                    <Clock class="size-3.5 sm:size-4 text-indigo-600 shrink-0" />
                    <span class="text-xs sm:text-sm md:text-base font-bold font-mono text-slate-900 tracking-tight">
                        {{ currentTime }}
                    </span>
                </div>
            </div>
        </header>

        <!-- 2. Konten Utama: 2 Kolom Grid Pas di Layar (min-h-0 flex-1 overflow-hidden) -->
        <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-4 items-stretch pt-2.5 sm:pt-3 min-h-0 overflow-hidden">
            
            <!-- Sisi Kiri (7 Kolom): Card Antrean Sedang Dilayani (Banner Ruang Dokter + Glass Card) -->
            <div class="col-span-12 lg:col-span-7 flex flex-col min-h-0 h-full">
                <div class="flex-1 rounded-2xl overflow-hidden border border-slate-200/90 relative flex flex-col justify-between shadow-xs bg-white min-h-0">
                    <!-- Banner Full Memenuhi Seluruh Card -->
                    <div class="absolute inset-0 z-0 overflow-hidden">
                        <img
                            src="/images/backround_display.jpg"
                            alt="Ruang Pemeriksaan Dokter"
                            class="w-full h-full object-cover clinic-banner-breathing"
                        />
                        <!-- Soft Gradient Halus di atas Banner -->
                        <div class="absolute inset-0 bg-gradient-to-t from-white/95 via-white/55 to-white/70"></div>
                    </div>

                    <!-- Label Status & Countdown Atas -->
                    <div class="relative z-10 flex items-center justify-between p-3.5 sm:p-4 pb-0 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-600 text-white text-[10px] sm:text-xs font-bold uppercase tracking-wider shadow-xs">
                                Sedang Diperiksa
                            </span>
                            <div
                                v-if="activeQueue"
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-mono font-bold border backdrop-blur-md transition-colors"
                                :class="remainingSeconds <= 180
                                    ? 'bg-rose-50 text-rose-700 border-rose-300'
                                    : 'bg-white/80 text-indigo-700 border-indigo-200'"
                            >
                                <Hourglass class="size-3 animate-spin" style="animation-duration: 4s;" />
                                <span>Sisa: {{ formattedCountdown }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] sm:text-xs text-slate-700 font-medium bg-white/70 backdrop-blur-xs px-2.5 py-0.5 rounded-md border border-white/60">
                            {{ activeQueue?.doctor?.doctor_name ? `${activeQueue.doctor.doctor_name} (${activeQueue.doctor.specialization})` : 'Poli Rawat Jalan' }}
                        </span>
                    </div>

                    <!-- Foreground: Card Nomor Antrean Glassmorphism -->
                    <div v-if="activeQueue" class="relative z-10 my-auto px-4 py-2 sm:py-3 flex flex-col items-center text-center">
                        <div class="w-full max-w-md bg-white/55 backdrop-blur-xl border border-white/80 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-[0_15px_35px_rgba(79,70,229,0.1)] ring-1 ring-white/60">
                            <p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-indigo-700/80 mb-0.5">
                                Nomor Antrean Panggilan
                            </p>
                            <!-- Nomor Antrean Font Monospace Besar -->
                            <div class="text-5xl sm:text-6xl md:text-7xl font-black font-mono tracking-tight text-indigo-600 my-0.5 drop-shadow-xs">
                                {{ activeQueue.queue_number }}
                            </div>
                            <!-- Nama Pasien -->
                            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1 truncate">
                                {{ activeQueue.patient?.patient_name }}
                            </h2>
                            <div class="mt-2 flex items-center justify-center gap-2.5 text-[11px] sm:text-xs text-slate-600 font-mono">
                                <span>Sesi: <strong class="text-slate-900">{{ activeQueue.session === 'morning' ? 'Pagi' : 'Sore' }}</strong></span>
                                <span>•</span>
                                <span>Estimasi: <strong class="text-slate-900">{{ activeQueue.estimate_time || '-' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- State Kosong jika belum ada panggilan aktif -->
                    <div v-else class="relative z-10 my-auto p-4 text-center">
                        <div class="inline-block bg-white/70 backdrop-blur-xl border border-white/80 rounded-2xl p-5 max-w-xs sm:max-w-sm shadow-sm">
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Belum Ada Panggilan Aktif</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 leading-relaxed">
                                Silakan bersiap di ruang tunggu, dokter akan segera memanggil nomor antrean Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Bar di depan banner -->
                    <div class="relative z-10 p-3 sm:p-4 pt-0 flex items-center justify-between text-[10px] sm:text-xs text-slate-500 shrink-0">
                        <span>Klinik Pratama</span>
                        <span>Harap bersiap saat nomor antrean tertera</span>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan (5 Kolom): Daftar Antrean Selanjutnya (Card Kompak) -->
            <div class="col-span-12 lg:col-span-5 flex flex-col min-h-0 h-full">
                <div class="flex-1 bg-white border border-slate-200/90 rounded-2xl p-3.5 sm:p-4 md:p-5 flex flex-col justify-between overflow-hidden shadow-xs min-h-0">
                    
                    <!-- Sub-Header Kanan -->
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 shrink-0">
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-900">
                                Antrean Selanjutnya
                            </h3>
                            <p class="text-[10px] sm:text-xs text-slate-500">Ruang Tunggu Pasien</p>
                        </div>
                        <span class="text-[10px] sm:text-xs font-mono font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                            {{ (props.nextQueues || []).length }} Menunggu
                        </span>
                    </div>

                    <!-- List Card Antrean Selanjutnya (Kompak & Auto-fit) -->
                    <div v-if="upcomingQueues.length > 0" class="flex-1 flex flex-col justify-center gap-2 py-2 min-h-0 overflow-y-auto">
                        <div
                            v-for="(queue, index) in upcomingQueues"
                            :key="queue.queue_id"
                            class="p-2.5 sm:p-3 rounded-xl border flex items-center justify-between transition-all shrink-0"
                            :class="index === 0
                                ? 'bg-indigo-600 border-indigo-600 text-white shadow-xs'
                                : 'bg-slate-900 border-slate-800 text-white'"
                        >
                            <!-- Kiri: Nomor Antrean Box Solid & Info Pasien Rapi -->
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div
                                    class="shrink-0 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-lg font-mono font-bold text-xs sm:text-sm tracking-tight flex items-center justify-center"
                                    :class="index === 0
                                        ? 'bg-white text-indigo-950 shadow-2xs'
                                        : 'bg-indigo-600 text-white'"
                                >
                                    {{ queue.queue_number }}
                                </div>

                                <div class="min-w-0">
                                    <h4 class="font-bold text-xs sm:text-sm leading-snug truncate" :class="index === 0 ? 'text-white' : 'text-slate-100'">
                                        {{ queue.patient?.patient_name }}
                                    </h4>
                                    <p class="text-[10px] sm:text-[11px] font-mono mt-0.5 truncate" :class="index === 0 ? 'text-indigo-100' : 'text-slate-400'">
                                        Est: {{ queue.estimate_time }} • Sesi {{ queue.session === 'morning' ? 'Pagi' : 'Sore' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Kanan: Status Bersiap / Urutan -->
                            <div class="shrink-0 pl-2">
                                <span
                                    v-if="index === 0"
                                    class="px-2 py-0.5 rounded-md text-[10px] sm:text-xs font-bold bg-white text-indigo-900 shadow-2xs"
                                >
                                    Bersiap
                                </span>
                                <span
                                    v-else
                                    class="text-[10px] sm:text-xs font-medium text-slate-400"
                                >
                                    Ke-{{ index + 1 }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Jika Tidak Ada Antrean Menunggu -->
                    <div v-else class="flex-1 flex flex-col items-center justify-center text-center p-4 text-slate-400 my-auto">
                        <CheckCircle2 class="size-7 text-emerald-500 mb-1.5" />
                        <p class="font-semibold text-xs sm:text-sm text-slate-800">Tidak Ada Antrean Menunggu</p>
                        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Semua pasien pada sesi ini telah dipanggil.</p>
                    </div>

                    <!-- Footer Note Kanan -->
                    <div class="pt-2 border-t border-slate-100 text-center text-[10px] sm:text-[11px] text-slate-400 shrink-0">
                        Harap perhatikan nomor panggilan pada layar ini
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
