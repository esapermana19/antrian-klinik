<script setup>
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import {
    Clock,
    Users,
    Maximize,
    Minimize,
    ArrowLeft,
    CheckCircle2,
    Calendar,
    Sparkles,
} from 'lucide-vue-next';

defineOptions({
    layout: null, // Standalone Kiosk Mode
});

const props = defineProps({
    currentQueue: Object,
    nextQueues: Array,
});

const currentTime = ref('');
const currentDate = ref('');
const isFullscreen = ref(false);

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

// Ambil maksimal 3 orang antrean berikutnya
const threeNextQueues = computed(() => {
    return (props.nextQueues || []).slice(0, 3);
});

let clockInterval;
let reloadInterval;

onMounted(() => {
    updateClock();
    clockInterval = setInterval(updateClock, 1000);
    reloadInterval = setInterval(() => {
        router.reload({ only: ['currentQueue', 'nextQueues'] });
    }, 3000);
    document.addEventListener('fullscreenchange', handleFullscreenChange);
});

onUnmounted(() => {
    clearInterval(clockInterval);
    clearInterval(reloadInterval);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
});
</script>

<template>
    <Head title="Display Papan Antrean Pasien" />

    <div class="h-screen w-screen bg-slate-50 text-slate-800 flex flex-col p-4 md:p-6 overflow-hidden font-sans select-none relative">
        <!-- Subtle Background Glow (Sesuai Halaman Lain) -->
        <div class="fixed inset-0 pointer-events-none -z-10">
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-purple-200/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-10 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
        </div>

        <!-- 1. Header Display: Bersih & Terang (Sama seperti Halaman Utama) -->
        <header class="flex items-center justify-between pb-4 border-b border-slate-200/80 shrink-0">
            <div class="flex items-center gap-3.5">
                <Link
                    href="/dashboard"
                    class="size-10 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition"
                    title="Kembali ke Dashboard"
                >
                    <ArrowLeft class="size-4" />
                </Link>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                        Papan Antrean Poliklinik
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="size-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            LIVE
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                        <span class="font-medium text-slate-600">Poliklinik Rawat Jalan</span>
                        <span>•</span>
                        <span>{{ currentDate }}</span>
                    </p>
                </div>
            </div>

            <!-- Jam Digital & Tombol Fullscreen TV -->
            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    @click="toggleFullscreen"
                    class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs text-xs font-semibold"
                    title="Mode Layar Penuh (TV Kiosk)"
                >
                    <component :is="isFullscreen ? Minimize : Maximize" class="size-3.5 text-indigo-600" />
                    <span class="hidden sm:inline">{{ isFullscreen ? 'Keluar Fullscreen' : 'Layar Penuh TV' }}</span>
                </button>

                <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-2xs">
                    <Clock class="size-4 text-indigo-600" />
                    <span class="text-base md:text-lg font-bold font-mono text-slate-900 tracking-tight">
                        {{ currentTime }}
                    </span>
                </div>
            </div>
        </header>

        <!-- 2. Main Content Grid: Proporsional & Rapi di Segala Ukuran Layar -->
        <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch pt-4 min-h-0">
            <!-- Left 7 Cols: Banner Antrean yang Sedang Diperiksa + Ilustrasi Kartun Sederhana -->
            <div class="lg:col-span-7 flex flex-col min-h-0">
                <div class="flex-1 bg-white border border-slate-200/90 rounded-2xl shadow-xs p-6 md:p-8 flex flex-col justify-between overflow-hidden relative">
                    <!-- Top Subtitle -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-indigo-600 animate-pulse"></span>
                            Ruang Konsultasi Dokter
                        </span>
                        <span class="text-xs font-medium text-slate-400">
                            Pemeriksaan Aktif
                        </span>
                    </div>

                    <!-- Banner Hero: Kartun Dokter & Keterangan Pasien (Tampil Harmonis & Bersih) -->
                    <div v-if="props.currentQueue" class="my-auto py-4 flex flex-col items-center text-center">
                        <!-- Kartun Sederhana Ilustrasi Dokter (Gaya Vector Flat Bersih seperti Referensi) -->
                        <div class="relative w-full max-w-[420px] h-48 md:h-56 mb-4 flex items-center justify-center">
                            <!-- Background Circle Soft (Sesuai Style Kartun di Contoh Gambar) -->
                            <div class="absolute inset-0 m-auto size-44 md:size-52 rounded-full bg-indigo-50 border border-indigo-100/60 -z-0"></div>

                            <svg
                                viewBox="0 0 400 240"
                                class="w-full h-full relative z-10 drop-shadow-sm select-none"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <!-- Lantai Minimalis -->
                                <ellipse cx="200" cy="220" rx="160" ry="12" fill="#e2e8f0" opacity="0.7" />

                                <!-- Ranjang Periksa Pasien Sederhana -->
                                <g transform="translate(210, 110)">
                                    <rect x="0" y="55" width="130" height="14" rx="4" fill="#6366f1" />
                                    <rect x="10" y="69" width="6" height="42" rx="2" fill="#64748b" />
                                    <rect x="114" y="69" width="6" height="42" rx="2" fill="#64748b" />
                                    <rect x="90" y="32" width="35" height="14" rx="6" transform="rotate(-25 90 32)" fill="#ffffff" stroke="#cbd5e1" />
                                </g>

                                <!-- Karakter Pasien (Duduk santai di ranjang periksa) -->
                                <g transform="translate(230, 75)">
                                    <!-- Badan Pasien (Baju Ungu Proyek) -->
                                    <path d="M 20,48 L 45,48 L 48,92 L 18,92 Z" fill="#8b5cf6" rx="4" />
                                    <!-- Celana Pasien -->
                                    <path d="M 18,92 L 20,128 L 32,128 L 30,92 Z" fill="#334155" />
                                    <path d="M 33,92 L 35,128 L 47,128 L 45,92 Z" fill="#1e293b" />
                                    <!-- Kepala Pasien -->
                                    <circle cx="33" cy="28" r="16" fill="#fed7aa" />
                                    <!-- Rambut -->
                                    <path d="M 18,28 C 18,14 26,12 35,12 C 45,12 48,18 48,28 C 42,22 36,22 30,24 Z" fill="#451a03" />
                                    <!-- Senyum & Mata Pasien -->
                                    <circle cx="27" cy="28" r="2" fill="#451a03" />
                                    <path d="M 24,34 Q 28,37 32,34" stroke="#ea580c" stroke-width="1.5" stroke-linecap="round" fill="none" />
                                </g>

                                <!-- Karakter Dokter (Memeriksa dengan Stetoskop) -->
                                <g transform="translate(100, 50)">
                                    <!-- Badan & Jas Putih Dokter -->
                                    <path d="M 35,68 L 65,68 L 72,165 L 28,165 Z" fill="#ffffff" stroke="#e2e8f0" stroke-width="1.5" />
                                    <path d="M 42,68 L 58,68 L 56,105 L 44,105 Z" fill="#4f46e5" /> <!-- Kemeja dalam -->
                                    <!-- Celana Dokter -->
                                    <rect x="33" y="165" width="12" height="48" rx="3" fill="#1e293b" />
                                    <rect x="55" y="165" width="12" height="48" rx="3" fill="#0f172a" />
                                    <!-- Sepatu -->
                                    <ellipse cx="38" cy="214" rx="10" ry="5" fill="#0f172a" />
                                    <ellipse cx="62" cy="214" rx="10" ry="5" fill="#0f172a" />
                                    <!-- Kepala Dokter -->
                                    <circle cx="50" cy="42" r="18" fill="#fcd34d" />
                                    <!-- Rambut Dokter -->
                                    <path d="M 32,38 C 32,22 42,20 52,20 C 64,20 68,28 68,38 C 60,30 52,30 45,32 Z" fill="#1e293b" />
                                    <!-- Kacamata & Senyum -->
                                    <circle cx="56" cy="40" r="5" stroke="#4f46e5" stroke-width="1.5" fill="#ffffff" fill-opacity="0.3" />
                                    <circle cx="57" cy="40" r="1.5" fill="#1e293b" />
                                    <path d="M 52,50 Q 56,53 60,50" stroke="#b45309" stroke-width="1.5" stroke-linecap="round" fill="none" />
                                    <!-- ID Card Dokter -->
                                    <rect x="35" y="85" width="10" height="14" rx="2" fill="#7c3aed" />

                                    <!-- Animasi Tangan Dokter Memeriksa dengan Stetoskop (Gerakan Halus) -->
                                    <g class="doctor-arm-simple">
                                        <!-- Lengan Jas Putih -->
                                        <path d="M 64,75 Q 85,85 105,92 L 132,95" stroke="#ffffff" stroke-width="12" stroke-linecap="round" fill="none" />
                                        <!-- Tangan Dokter -->
                                        <circle cx="134" cy="95" r="6" fill="#fcd34d" />
                                        <!-- Ujung Stetoskop menyentuh dada pasien -->
                                        <circle cx="140" cy="95" r="5" fill="#64748b" stroke="#334155" stroke-width="1.5" />
                                        <!-- Efek Denyut Periksa (Gelombang Halus) -->
                                        <circle cx="140" cy="95" r="10" fill="#8b5cf6" opacity="0.3" class="animate-ping" />
                                    </g>
                                    <!-- Selang Stetoskop -->
                                    <path d="M 50,56 C 52,80 88,115 133,98" stroke="#475569" stroke-width="2" fill="none" stroke-linecap="round" />
                                </g>

                                <!-- Elemen Dekoratif Aksen Minimalis -->
                                <circle cx="340" cy="65" r="12" fill="#ede9fe" />
                                <path d="M 336,65 L 344,65 M 340,61 L 340,69" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </div>

                        <!-- Keterangan Nomor Antrean & Nama Pasien yang Sedang Diperiksa -->
                        <div class="w-full max-w-lg bg-slate-50 border border-slate-200/80 rounded-2xl p-5 shadow-2xs">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Nomor Antrean Aktif
                            </p>
                            <!-- Nomor Antrean Bersih & Besar -->
                            <div class="text-6xl md:text-7xl font-black font-mono tracking-tight text-indigo-600 my-1">
                                {{ props.currentQueue.queue_number }}
                            </div>
                            <!-- Nama Pasien -->
                            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-2">
                                {{ props.currentQueue.patient?.patient_name }}
                            </h2>
                            <p class="text-xs text-slate-500 mt-1 font-mono">
                                NIK: {{ props.currentQueue.patient?.nik || '-' }} • Gender: {{ props.currentQueue.patient?.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </p>
                        </div>
                    </div>

                    <!-- Empty State jika belum ada antrean dipanggil -->
                    <div v-else class="my-auto py-16 text-center text-slate-400">
                        <div class="size-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                            <Sparkles class="size-8" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Belum Ada Panggilan Aktif</h3>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto mt-1">
                            Silakan menunggu, nomor antrean pertama akan segera dipanggil oleh dokter ke ruang periksa.
                        </p>
                    </div>

                    <!-- Bottom Clinic Note -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <span>Poli Rawat Jalan Klinik</span>
                        <span>Harap bersiap di dekat pintu periksa saat nomor Anda tertera</span>
                    </div>
                </div>
            </div>

            <!-- Right 5 Cols: List 3 Orang Selanjutnya (Bersih & Sederhana) -->
            <div class="lg:col-span-5 flex flex-col min-h-0 space-y-3">
                <div class="flex items-center justify-between pb-1">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <Users class="size-4.5 text-indigo-600" />
                        3 Antrean Selanjutnya
                    </h3>
                    <span class="text-xs font-semibold text-slate-500 bg-white border border-slate-200 px-3 py-1 rounded-full shadow-2xs">
                        Ruang Tunggu
                    </span>
                </div>

                <!-- List 3 Card Antrean Selanjutnya -->
                <div v-if="threeNextQueues.length > 0" class="flex-1 flex flex-col space-y-3 overflow-y-auto pr-0.5">
                    <div
                        v-for="(queue, index) in threeNextQueues"
                        :key="queue.queue_id"
                        class="p-4 rounded-xl border bg-white flex items-center justify-between transition-all duration-200 shadow-2xs"
                        :class="index === 0
                            ? 'border-indigo-300 ring-2 ring-indigo-500/10'
                            : 'border-slate-200'"
                    >
                        <div class="flex items-center gap-3.5">
                            <!-- Nomor Antrean Box -->
                            <div
                                class="size-12 rounded-xl flex items-center justify-center font-mono font-bold text-lg"
                                :class="index === 0
                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30'
                                    : 'bg-slate-100 text-slate-700'"
                            >
                                {{ queue.queue_number }}
                            </div>

                            <!-- Detail Pasien -->
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm md:text-base leading-snug">
                                    {{ queue.patient?.patient_name }}
                                </h4>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">
                                    Estimasi: <strong class="text-slate-600">{{ queue.estimate_time }}</strong> • Sesi {{ queue.session === 'morning' ? 'Pagi' : 'Sore' }}
                                </p>
                            </div>
                        </div>

                        <!-- Urutan / Indikator Bersiap -->
                        <div class="text-right">
                            <span
                                v-if="index === 0"
                                class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"
                            >
                                Urutan #1 Bersiap
                            </span>
                            <span
                                v-else
                                class="inline-block px-2.5 py-1 rounded-lg text-xs font-medium text-slate-500 bg-slate-50 border border-slate-200"
                            >
                                Urutan #{{ index + 1 }}
                            </span>
                        </div>
                    </div>

                    <!-- Slot Kosong jika antrean belum genap 3 -->
                    <div
                        v-for="emptySlot in (3 - threeNextQueues.length)"
                        :key="'slot-' + emptySlot"
                        class="p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/50 flex items-center justify-center text-slate-400 text-xs py-6"
                    >
                        Slot Antrean Belum Ada Pendaftar
                    </div>
                </div>

                <!-- State jika tidak ada antrean menunggu -->
                <div v-else class="flex-1 p-6 border border-slate-200 bg-white rounded-xl flex flex-col items-center justify-center text-center text-slate-400 shadow-2xs">
                    <CheckCircle2 class="size-10 text-emerald-500 mb-2" />
                    <p class="font-bold text-sm text-slate-800">Tidak Ada Antrean Menunggu</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-xs">Semua pasien pada sesi ini telah dipanggil atau belum ada pasien baru.</p>
                </div>

                <!-- Info Banner Sederhana di Bagian Bawah -->
                <div class="p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-100 text-xs text-indigo-800 leading-relaxed">
                    Pasien nomor berikutnya dimohon tetap berada di ruang tunggu agar tidak terlewat saat nomor Anda dipanggil ke ruang dokter.
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
@keyframes doctorArmSubtle {
    0%, 100% {
        transform: rotate(0deg);
    }
    50% {
        transform: rotate(-3deg);
    }
}

.doctor-arm-simple {
    animation: doctorArmSubtle 3.5s ease-in-out infinite;
    transform-origin: 64px 75px;
}
</style>
