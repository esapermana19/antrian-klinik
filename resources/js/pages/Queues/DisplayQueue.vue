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
    clinic: Object,
});

const page = usePage();
const clinicName = computed(
    () =>
        props.clinic?.clinic_name ||
        page.props.clinic?.clinic_name ||
        'ANTRIAN KLINIK',
);
const clinicLogoUrl = computed(
    () => props.clinic?.clinic_logo_url || page.props.clinic?.clinic_logo_url,
);
const clinicBannerUrl = computed(
    () =>
        props.clinic?.clinic_banner_url ||
        page.props.clinic?.clinic_banner_url ||
        '/images/backround_display.jpg',
);

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
            queue.doctor?.doctor_name,
        );
    }
};

// Sinkronkan bila props dari server berubah
watch(
    () => props.currentQueue,
    (newVal) => {
        activeQueue.value = newVal;
        if (newVal) {
            checkAndAnnounce(newVal);
        }
    },
);
watch(
    () => props.nextQueues,
    (newVal) => {
        upcomingList.value = newVal || [];
    },
);

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
watch(
    () => activeQueue.value?.queue_id,
    () => {
        initCountdown();
    },
);

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
            headers: { Accept: 'application/json' },
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
    <div
        class="box-border flex h-screen w-screen flex-col overflow-hidden bg-slate-50 p-3 font-sans text-slate-800 select-none sm:p-4 md:p-5"
    >
        <!-- 1. Header Display TV: Ramping & Kompak (shrink-0) -->
        <header
            class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 pb-2.5 sm:pb-3"
        >
            <!-- Sisi Kiri: Tombol Back & Branding Klinik -->
            <div class="flex min-w-0 items-center gap-2.5">
                <Link
                    href="/dashboard"
                    class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 sm:size-9"
                    title="Kembali ke Dashboard"
                >
                    <ArrowLeft class="size-4" />
                </Link>
                <img
                    v-if="clinicLogoUrl"
                    :src="clinicLogoUrl"
                    alt="Logo Klinik"
                    class="h-8 w-auto max-w-[100px] shrink-0 object-contain sm:h-9"
                />
                <div class="min-w-0">
                    <h1
                        class="truncate text-base leading-tight font-bold tracking-tight text-slate-900 uppercase sm:text-lg md:text-xl"
                    >
                        {{ clinicName }}
                    </h1>
                    <p
                        class="truncate text-[10px] font-medium text-slate-500 sm:text-xs"
                    >
                        Papan Antrean Poliklinik • {{ currentDate }}
                    </p>
                </div>
            </div>

            <!-- Sisi Kanan: Filter Dokter/Poli, Kontrol Suara, Jam Digital & Fullscreen Button -->
            <div class="flex shrink-0 items-center gap-2">
                <!-- Tombol Suara TTS (Mute / Unmute) -->
                <button
                    type="button"
                    @click="toggleMute"
                    class="flex cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[11px] font-medium shadow-2xs transition sm:px-3 sm:text-xs"
                    :class="
                        isMuted
                            ? 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100'
                            : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                    "
                    :title="
                        isMuted
                            ? 'Suara Panggilan Dinonaktifkan (Klik untuk Aktifkan)'
                            : 'Suara Panggilan Aktif (Klik untuk Mute)'
                    "
                >
                    <component
                        :is="isMuted ? VolumeX : Volume2"
                        class="size-3.5"
                        :class="isMuted ? 'text-rose-600' : 'text-indigo-600'"
                    />
                    <span class="hidden sm:inline">{{
                        isMuted ? 'Mute' : 'Suara Aktif'
                    }}</span>
                </button>

                <!-- Dropdown Poli / Dokter -->
                <div
                    v-if="doctors && doctors.length > 0"
                    class="flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1 shadow-2xs"
                >
                    <select
                        v-model="selectedDoctor"
                        @change="changeDoctor(selectedDoctor)"
                        class="cursor-pointer border-0 bg-transparent p-0 text-[11px] font-medium text-slate-800 focus:outline-none sm:text-xs"
                    >
                        <option value="">Semua Poli / Dokter</option>
                        <option
                            v-for="doc in doctors"
                            :key="doc.doctor_id"
                            :value="doc.doctor_id"
                        >
                            {{ doc.doctor_name }} ({{ doc.specialization }})
                        </option>
                    </select>
                </div>

                <button
                    type="button"
                    @click="toggleFullscreen"
                    class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-medium text-slate-700 shadow-2xs transition hover:bg-slate-50 sm:px-3 sm:text-xs"
                    title="Toggle Fullscreen"
                >
                    <component
                        :is="isFullscreen ? Minimize : Maximize"
                        class="size-3.5 text-indigo-600"
                    />
                    <span class="hidden sm:inline">{{
                        isFullscreen ? 'Keluar' : 'Layar Penuh TV'
                    }}</span>
                </button>

                <div
                    class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 shadow-2xs sm:gap-2 sm:px-3"
                >
                    <Clock
                        class="size-3.5 shrink-0 text-indigo-600 sm:size-4"
                    />
                    <span
                        class="font-mono text-xs font-bold tracking-tight text-slate-900 sm:text-sm md:text-base"
                    >
                        {{ currentTime }}
                    </span>
                </div>
            </div>
        </header>

        <!-- 2. Konten Utama: 2 Kolom Grid Pas di Layar (min-h-0 flex-1 overflow-hidden) -->
        <main
            class="grid min-h-0 flex-1 grid-cols-1 items-stretch gap-3 overflow-hidden pt-2.5 sm:gap-4 sm:pt-3 lg:grid-cols-12"
        >
            <!-- Sisi Kiri (7 Kolom): Card Antrean Sedang Dilayani (Banner Ruang Dokter + Glass Card) -->
            <div class="col-span-12 flex h-full min-h-0 flex-col lg:col-span-7">
                <div
                    class="relative flex min-h-0 flex-1 flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs"
                >
                    <!-- Banner Full Memenuhi Seluruh Card -->
                    <div class="absolute inset-0 z-0 overflow-hidden">
                        <img
                            :src="clinicBannerUrl"
                            alt="Ruang Pemeriksaan Dokter"
                            class="clinic-banner-breathing h-full w-full object-cover"
                        />
                        <!-- Soft Gradient Halus di atas Banner -->
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-white/95 via-white/55 to-white/70"
                        ></div>
                    </div>

                    <!-- Label Status & Countdown Atas -->
                    <div
                        class="relative z-10 flex shrink-0 items-center justify-between p-3.5 pb-0 sm:p-4"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="rounded-full bg-indigo-600 px-2.5 py-0.5 text-[10px] font-bold tracking-wider text-white uppercase shadow-xs sm:text-xs"
                            >
                                Sedang Diperiksa
                            </span>
                            <div
                                v-if="activeQueue"
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 font-mono text-[10px] font-bold backdrop-blur-md transition-colors sm:text-xs"
                                :class="
                                    remainingSeconds <= 180
                                        ? 'border-rose-300 bg-rose-50 text-rose-700'
                                        : 'border-indigo-200 bg-white/80 text-indigo-700'
                                "
                            >
                                <Hourglass
                                    class="size-3 animate-spin"
                                    style="animation-duration: 4s"
                                />
                                <span>Sisa: {{ formattedCountdown }}</span>
                            </div>
                        </div>
                        <span
                            class="rounded-md border border-white/60 bg-white/70 px-2.5 py-0.5 text-[10px] font-medium text-slate-700 backdrop-blur-xs sm:text-xs"
                        >
                            {{
                                activeQueue?.doctor?.doctor_name
                                    ? `${activeQueue.doctor.doctor_name} (${activeQueue.doctor.specialization})`
                                    : 'Poli Rawat Jalan'
                            }}
                        </span>
                    </div>

                    <!-- Foreground: Card Nomor Antrean Glassmorphism -->
                    <div
                        v-if="activeQueue"
                        class="relative z-10 my-auto flex flex-col items-center px-4 py-2 text-center sm:py-3"
                    >
                        <div
                            class="w-full max-w-md rounded-2xl border border-white/80 bg-white/55 p-4 shadow-[0_15px_35px_rgba(79,70,229,0.1)] ring-1 ring-white/60 backdrop-blur-xl sm:rounded-3xl sm:p-6"
                        >
                            <p
                                class="mb-0.5 text-[10px] font-bold tracking-widest text-indigo-700/80 uppercase sm:text-xs"
                            >
                                Nomor Antrean Panggilan
                            </p>
                            <!-- Nomor Antrean Font Monospace Besar -->
                            <div
                                class="my-0.5 font-mono text-5xl font-black tracking-tight text-indigo-600 drop-shadow-xs sm:text-6xl md:text-7xl"
                            >
                                {{ activeQueue.queue_number }}
                            </div>
                            <!-- Nama Pasien -->
                            <h2
                                class="mt-1 truncate text-xl font-extrabold text-slate-900 sm:text-2xl"
                            >
                                {{ activeQueue.patient?.patient_name }}
                            </h2>
                            <div
                                class="mt-2 flex items-center justify-center gap-2.5 font-mono text-[11px] text-slate-600 sm:text-xs"
                            >
                                <span
                                    >Sesi:
                                    <strong class="text-slate-900">{{
                                        activeQueue.session === 'morning'
                                            ? 'Pagi'
                                            : 'Sore'
                                    }}</strong></span
                                >
                                <span>•</span>
                                <span
                                    >Estimasi:
                                    <strong class="text-slate-900">{{
                                        activeQueue.estimate_time || '-'
                                    }}</strong></span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- State Kosong jika belum ada panggilan aktif -->
                    <div v-else class="relative z-10 my-auto p-4 text-center">
                        <div
                            class="inline-block max-w-xs rounded-2xl border border-white/80 bg-white/70 p-5 shadow-sm backdrop-blur-xl sm:max-w-sm"
                        >
                            <h3
                                class="text-sm font-bold text-slate-800 sm:text-base"
                            >
                                Belum Ada Panggilan Aktif
                            </h3>
                            <p
                                class="mt-1 text-[11px] leading-relaxed text-slate-500 sm:text-xs"
                            >
                                Silakan bersiap di ruang tunggu, dokter akan
                                segera memanggil nomor antrean Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Bar di depan banner -->
                    <div
                        class="relative z-10 flex shrink-0 items-center justify-between p-3 pt-0 text-[10px] text-slate-500 sm:p-4 sm:text-xs"
                    >
                        <span>Klinik Pratama</span>
                        <span>Harap bersiap saat nomor antrean tertera</span>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan (5 Kolom): Daftar Antrean Selanjutnya (Card Kompak) -->
            <div class="col-span-12 flex h-full min-h-0 flex-col lg:col-span-5">
                <div
                    class="flex min-h-0 flex-1 flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-3.5 shadow-xs sm:p-4 md:p-5"
                >
                    <!-- Sub-Header Kanan -->
                    <div
                        class="flex shrink-0 items-center justify-between border-b border-slate-100 pb-2.5"
                    >
                        <div>
                            <h3
                                class="text-xs font-bold tracking-wider text-slate-900 uppercase sm:text-sm"
                            >
                                Antrean Selanjutnya
                            </h3>
                            <p class="text-[10px] text-slate-500 sm:text-xs">
                                Ruang Tunggu Pasien
                            </p>
                        </div>
                        <span
                            class="rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-semibold text-slate-700 sm:text-xs"
                        >
                            {{ (props.nextQueues || []).length }} Menunggu
                        </span>
                    </div>

                    <!-- List Card Antrean Selanjutnya (Kompak & Auto-fit) -->
                    <div
                        v-if="upcomingQueues.length > 0"
                        class="flex min-h-0 flex-1 flex-col justify-center gap-2 overflow-y-auto py-2"
                    >
                        <div
                            v-for="(queue, index) in upcomingQueues"
                            :key="queue.queue_id"
                            class="flex shrink-0 items-center justify-between rounded-xl border p-2.5 transition-all sm:p-3"
                            :class="
                                index === 0
                                    ? 'border-indigo-600 bg-indigo-600 text-white shadow-xs'
                                    : 'border-slate-800 bg-slate-900 text-white'
                            "
                        >
                            <!-- Kiri: Nomor Antrean Box Solid & Info Pasien Rapi -->
                            <div class="flex min-w-0 items-center gap-2.5">
                                <div
                                    class="flex shrink-0 items-center justify-center rounded-lg px-2 py-1 font-mono text-xs font-bold tracking-tight sm:px-2.5 sm:py-1.5 sm:text-sm"
                                    :class="
                                        index === 0
                                            ? 'bg-white text-indigo-950 shadow-2xs'
                                            : 'bg-indigo-600 text-white'
                                    "
                                >
                                    {{ queue.queue_number }}
                                </div>

                                <div class="min-w-0">
                                    <h4
                                        class="truncate text-xs leading-snug font-bold sm:text-sm"
                                        :class="
                                            index === 0
                                                ? 'text-white'
                                                : 'text-slate-100'
                                        "
                                    >
                                        {{ queue.patient?.patient_name }}
                                    </h4>
                                    <p
                                        class="mt-0.5 truncate font-mono text-[10px] sm:text-[11px]"
                                        :class="
                                            index === 0
                                                ? 'text-indigo-100'
                                                : 'text-slate-400'
                                        "
                                    >
                                        Est: {{ queue.estimate_time }} • Sesi
                                        {{
                                            queue.session === 'morning'
                                                ? 'Pagi'
                                                : 'Sore'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Kanan: Status Bersiap / Urutan -->
                            <div class="shrink-0 pl-2">
                                <span
                                    v-if="index === 0"
                                    class="rounded-md bg-white px-2 py-0.5 text-[10px] font-bold text-indigo-900 shadow-2xs sm:text-xs"
                                >
                                    Bersiap
                                </span>
                                <span
                                    v-else
                                    class="text-[10px] font-medium text-slate-400 sm:text-xs"
                                >
                                    Ke-{{ index + 1 }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Jika Tidak Ada Antrean Menunggu -->
                    <div
                        v-else
                        class="my-auto flex flex-1 flex-col items-center justify-center p-4 text-center text-slate-400"
                    >
                        <CheckCircle2 class="mb-1.5 size-7 text-emerald-500" />
                        <p
                            class="text-xs font-semibold text-slate-800 sm:text-sm"
                        >
                            Tidak Ada Antrean Menunggu
                        </p>
                        <p class="mt-0.5 text-[10px] text-slate-400 sm:text-xs">
                            Semua pasien pada sesi ini telah dipanggil.
                        </p>
                    </div>

                    <!-- Footer Note Kanan -->
                    <div
                        class="shrink-0 border-t border-slate-100 pt-2 text-center text-[10px] text-slate-400 sm:text-[11px]"
                    >
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
    0%,
    100% {
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
