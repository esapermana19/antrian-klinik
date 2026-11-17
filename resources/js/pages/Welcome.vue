<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Stethoscope,
    Calendar,
    Phone,
    MapPin,
    ArrowRight,
    ChevronLeft,
    ChevronRight,
    LogIn,
    LayoutDashboard,
    Ticket,
    Baby,
    HeartPulse,
    Smile,
    BriefcaseMedical,
} from 'lucide-vue-next';

interface DoctorSchedule {
    schedule_id: number;
    doctor_id: number;
    schedule_code: string;
    day: string;
    session: string;
    opening_hours: string;
    closing_hours: string;
    max_quota: number;
    status: string;
}

interface Doctor {
    doctor_id: number;
    doctor_name: string;
    specialization: string;
    str_number?: string | null;
    phone_number?: string | null;
    photo_path?: string | null;
    photo_url?: string | null;
    queue_prefix?: string | null;
    practice_schedules?: DoctorSchedule[];
}

interface ScheduleWithDoctor extends DoctorSchedule {
    doctor?: Doctor;
}

const props = defineProps<{
    clinic: {
        clinic_name: string;
        clinic_address: string;
        clinic_phone: string;
        clinic_logo_url?: string | null;
        clinic_banner_url?: string | null;
        queue_prefix?: string;
    };
    landing: {
        hero_badge?: string;
        hero_title?: string;
        hero_subtitle?: string;
        cta_primary_text?: string;
        cta_secondary_text?: string;
        features_section_title?: string;
        features_section_subtitle?: string;
        doctors_section_title?: string;
        doctors_section_subtitle?: string;
        schedules_section_title?: string;
        schedules_section_subtitle?: string;
        emergency_contact?: string;
    };
    doctors: Doctor[];
    schedules: ScheduleWithDoctor[];
    specialties: string[];
    stats?: {
        total_patients: number;
        total_doctors: number;
        total_specialties: number;
        total_schedules: number;
    };
}>();

// Map hari bahasa Inggris & Indonesia agar normalisasi konsisten
const dayTranslation: Record<string, string> = {
    monday: 'Senin',
    tuesday: 'Selasa',
    wednesday: 'Rabu',
    thursday: 'Kamis',
    friday: 'Jumat',
    saturday: 'Sabtu',
    sunday: 'Minggu',
    senin: 'Senin',
    selasa: 'Selasa',
    rabu: 'Rabu',
    kamis: 'Kamis',
    jumat: 'Jumat',
    sabtu: 'Sabtu',
    minggu: 'Minggu',
};

const sessionTranslation: Record<string, string> = {
    morning: 'Pagi',
    afternoon: 'Sore',
    evening: 'Malam',
    pagi: 'Pagi',
    sore: 'Sore',
    malam: 'Malam',
};

const formatDay = (rawDay?: string) => {
    if (!rawDay) return '-';
    const key = rawDay.trim().toLowerCase();
    return dayTranslation[key] || rawDay;
};

const formatSession = (rawSession?: string) => {
    if (!rawSession) return '-';
    const key = rawSession.trim().toLowerCase();
    return sessionTranslation[key] || rawSession;
};

const formatTime = (timeStr?: string) => {
    if (!timeStr) return '';
    return timeStr.substring(0, 5);
};

const getSpecialtyIcon = (name: string) => {
    const lower = name.toLowerCase();
    if (lower.includes('anak')) return Baby;
    if (lower.includes('dalam') || lower.includes('jantung')) return HeartPulse;
    if (lower.includes('gigi')) return Smile;
    return BriefcaseMedical;
};

const heroBannerImage = computed(() => {
    return props.clinic?.clinic_banner_url || '/images/backround_display.jpg';
});
</script>

<template>
    <Head :title="clinic.clinic_name || 'Klinik Pratama Sehat'">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <!-- Main Container: Background Ambient Smooth Silky Wave & Mesh Gradasi Persis Seperti Halaman Login -->
    <div
        class="relative min-h-screen overflow-x-hidden bg-[#f2f6fc] font-sans text-slate-900 antialiased select-none selection:bg-indigo-600 selection:text-white"
    >
        <!-- Background Ambient Mesh Silky Gradient Wave (Identik Halaman Login) -->
        <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
            <!-- Sky Blue & Lavender Soft Background Radial -->
            <div
                class="pointer-events-none absolute -top-1/4 left-0 h-[600px] w-full bg-radial from-[#e0efff]/80 via-[#edf4fd]/60 to-transparent blur-3xl"
            />

            <!-- Gelombang Halus Biru Keunguan di Tengah Menyeberang ke Bawah (Wave Flow) -->
            <div
                class="absolute top-[25%] -left-[10%] h-[55%] w-[120%] -rotate-[7deg] transform rounded-[100%] bg-gradient-to-r from-blue-300/30 via-indigo-400/25 to-purple-400/20 blur-[100px]"
            />
            <div
                class="absolute top-[48%] -left-[5%] h-[40%] w-[110%] -rotate-[4deg] transform rounded-[100%] bg-gradient-to-r from-blue-400/20 via-indigo-500/15 to-blue-300/10 blur-[80px]"
            />

            <!-- Pita Gelombang Halus Vektor (Subtle Elegant Ribbon Curve) -->
            <svg
                class="pointer-events-none absolute inset-0 h-full w-full opacity-60"
                viewBox="0 0 1440 900"
                fill="none"
                preserveAspectRatio="none"
            >
                <path
                    d="M-100,560 C320,680 580,480 920,530 C1200,570 1350,460 1550,500 L1550,950 L-100,950 Z"
                    fill="url(#wave-gradient-1)"
                    opacity="0.3"
                />
                <path
                    d="M-50,610 C380,720 700,520 1020,580 C1280,630 1420,530 1550,560 L1550,950 L-50,950 Z"
                    fill="url(#wave-gradient-2)"
                    opacity="0.4"
                />
                <defs>
                    <linearGradient
                        id="wave-gradient-1"
                        x1="0"
                        y1="0"
                        x2="1"
                        y2="0"
                    >
                        <stop
                            offset="0%"
                            stop-color="#bfdbfe"
                            stop-opacity="0.8"
                        />
                        <stop
                            offset="50%"
                            stop-color="#c7d2fe"
                            stop-opacity="0.6"
                        />
                        <stop
                            offset="100%"
                            stop-color="#ddd6fe"
                            stop-opacity="0.7"
                        />
                    </linearGradient>
                    <linearGradient
                        id="wave-gradient-2"
                        x1="0"
                        y1="0"
                        x2="1"
                        y2="0"
                    >
                        <stop
                            offset="0%"
                            stop-color="#93c5fd"
                            stop-opacity="0.5"
                        />
                        <stop
                            offset="60%"
                            stop-color="#818cf8"
                            stop-opacity="0.4"
                        />
                        <stop
                            offset="100%"
                            stop-color="#a78bfa"
                            stop-opacity="0.5"
                        />
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <!-- 1. FLOATING NAVIGATION BAR (Glass Pill Island di Bagian Atas) -->
        <div
            class="pointer-events-none fixed inset-x-0 top-5 z-50 flex justify-center px-4"
        >
            <header
                class="pointer-events-auto flex h-14 w-full max-w-[1180px] items-center justify-between rounded-full border border-white/80 bg-white/80 px-5 shadow-[0_8px_30px_rgb(0,0,0,0.06)] backdrop-blur-xl transition-all"
            >
                <!-- Brand Title / Logo -->
                <Link href="/" class="group flex items-center gap-3">
                    <div
                        class="flex size-8 items-center justify-center rounded-full bg-slate-900 text-xs font-bold tracking-wider text-white shadow-2xs transition-colors group-hover:bg-indigo-600"
                    >
                        <img
                            v-if="clinic.clinic_logo_url"
                            :src="clinic.clinic_logo_url"
                            :alt="clinic.clinic_name"
                            class="size-full rounded-full object-contain p-1"
                        />
                        <span
                            v-else
                            class="font-mono text-[10px] font-bold uppercase"
                        >
                            {{ (clinic.clinic_name || 'KLN').substring(0, 3) }}
                        </span>
                    </div>
                    <span
                        class="text-xs font-bold tracking-tight text-slate-900 uppercase sm:text-sm"
                    >
                        {{ clinic.clinic_name || 'Klinik Sehat Medika' }}
                    </span>
                </Link>

                <!-- Center Nav Links -->
                <nav
                    class="hidden items-center gap-7 text-xs font-medium text-slate-600 md:flex"
                >
                    <a
                        href="#layanan"
                        class="transition-colors hover:text-slate-950"
                        >Layanan</a
                    >
                    <a
                        href="#spesialisasi"
                        class="transition-colors hover:text-slate-950"
                        >Spesialisasi</a
                    >
                    <a
                        href="#dokter"
                        class="transition-colors hover:text-slate-950"
                        >Dokter</a
                    >
                    <a
                        href="#lokasi"
                        class="transition-colors hover:text-slate-950"
                        >Kontak</a
                    >
                </nav>

                <!-- Right Action Buttons -->
                <div class="flex items-center gap-2">
                    <template v-if="$page.props.auth?.user">
                        <Link
                            href="/dashboard"
                            class="inline-flex h-8 items-center gap-1.5 rounded-full bg-slate-900 px-4 text-xs font-medium text-white shadow-2xs transition-all hover:bg-slate-800"
                        >
                            <LayoutDashboard class="size-3.5" />
                            <span>Dashboard</span>
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="hidden h-8 items-center gap-1.5 rounded-full border border-slate-200/80 bg-white/70 px-3.5 text-xs font-medium text-slate-700 transition-all hover:bg-white sm:inline-flex"
                        >
                            <LogIn class="size-3 text-slate-500" />
                            <span>Masuk</span>
                        </Link>
                        <Link
                            href="/queue"
                            class="inline-flex h-8 items-center gap-1.5 rounded-full bg-slate-900 px-4.5 text-xs font-medium text-white shadow-2xs transition-all hover:bg-slate-800"
                        >
                            <span>Daftar Antrean</span>
                            <ArrowRight class="size-3" />
                        </Link>
                    </template>
                </div>
            </header>
        </div>

        <!-- 2. HERO SECTION: Gambar Terang & Jelas (Tanpa Card-Card Statistik, Tipografi Editorial Elegan Persis Synora) -->
        <section
            class="relative flex min-h-[640px] items-center overflow-hidden pt-28 pb-16 lg:min-h-[720px]"
        >
            <!-- Background Image Asli (Terang & Jelas) -->
            <div class="absolute inset-0 z-0">
                <img
                    :src="heroBannerImage"
                    alt="Klinik Banner"
                    class="size-full object-cover object-center brightness-100 contrast-[1.02] filter"
                />
                <!-- Subtle Gradient Mask: Hanya di bagian teks kiri agar tulisan tetap terbaca jelas -->
                <div
                    class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/80 to-transparent sm:w-2/3 lg:w-1/2"
                ></div>
            </div>

            <div class="relative z-10 mx-auto w-full max-w-[1240px] px-6">
                <div
                    class="grid grid-cols-1 items-center gap-8 lg:grid-cols-12"
                >
                    <!-- Left Hero Content (Editorial Typography) -->
                    <div class="space-y-6 lg:col-span-7">
                        <div
                            class="text-[11px] font-bold tracking-[0.2em] text-slate-600 uppercase"
                        >
                            {{
                                landing.hero_badge ||
                                'MULTI-SPECIALTY MEDICAL CLINIC'
                            }}
                        </div>

                        <!-- Big Headline -->
                        <h1
                            class="max-w-2xl text-4xl leading-[1.02] font-extrabold tracking-[-0.03em] text-balance text-slate-950 uppercase sm:text-5xl md:text-6xl"
                        >
                            {{
                                landing.hero_title ||
                                'HEALTHCARE DESIGNED AROUND YOU'
                            }}
                        </h1>

                        <!-- Buttons Pill (Obsidian & Ghost Outline) -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <Link
                                href="/queue"
                                class="inline-flex h-11 items-center gap-2 rounded-full bg-slate-900 px-6 text-xs font-medium tracking-tight text-white shadow-md transition-all hover:scale-[1.01] hover:bg-slate-800 active:scale-[0.99]"
                            >
                                <Ticket class="size-3.5" />
                                <span>{{
                                    landing.cta_primary_text ||
                                    'Ambil Nomor Antrean'
                                }}</span>
                            </Link>
                            <a
                                href="#dokter"
                                class="inline-flex h-11 items-center gap-2 rounded-full border border-slate-300 bg-white/90 px-6 text-xs font-medium tracking-tight text-slate-800 shadow-2xs transition-all hover:bg-white"
                            >
                                <Stethoscope class="size-3.5 text-indigo-600" />
                                <span>{{
                                    landing.cta_secondary_text || 'Cari Dokter'
                                }}</span>
                            </a>
                        </div>

                        <!-- Bottom Editorial Note -->
                        <div class="max-w-md pt-6">
                            <p
                                class="text-xs leading-relaxed font-normal text-slate-600"
                            >
                                {{
                                    landing.hero_subtitle ||
                                    'Layanan rawat jalan terpadu dengan dokter umum dan spesialis profesional. Nikmati sistem pendaftaran dan pemantauan antrean tanpa perlu menghabiskan waktu menunggu berlama-lama di ruang tunggu klinik.'
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Right Stats Column (CLEAN MINIMALIST TANPA KARTU/CARD, Persis Seperti Referensi Synora) -->
                    <div
                        class="flex flex-col justify-center gap-8 text-right sm:text-right lg:col-span-5 lg:pl-16"
                    >
                        <!-- Stat 1 -->
                        <div class="space-y-0.5">
                            <div
                                class="text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl"
                            >
                                {{
                                    (stats?.total_patients ?? 0) > 0
                                        ? (stats?.total_patients ?? 0) + '+'
                                        : '20K+'
                                }}
                            </div>
                            <div
                                class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Pasien Terdaftar & Terlayani
                            </div>
                        </div>

                        <!-- Stat 2 -->
                        <div class="space-y-0.5">
                            <div
                                class="text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl"
                            >
                                {{
                                    doctors.length > 0 ? doctors.length : '30+'
                                }}+
                            </div>
                            <div
                                class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Dokter Bersertifikasi & Aktif
                            </div>
                        </div>

                        <!-- Stat 3 -->
                        <div class="space-y-0.5">
                            <div
                                class="text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl"
                            >
                                {{
                                    specialties.length > 0
                                        ? specialties.length
                                        : '18'
                                }}
                            </div>
                            <div
                                class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Poli & Bidang Spesialisasi
                            </div>
                        </div>

                        <!-- Stat 4 -->
                        <div class="space-y-0.5">
                            <div
                                class="text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl"
                            >
                                4.9/5
                            </div>
                            <div
                                class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Tingkat Kepuasan Pasien
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. SERVICES SECTION (Foto Minimalis dengan Overlay Gradien Halus) -->
        <section id="layanan" class="relative z-10 py-24">
            <div class="mx-auto max-w-[1240px] px-6">
                <!-- Section Header -->
                <div
                    class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end"
                >
                    <div class="max-w-2xl">
                        <h2
                            class="text-3xl leading-tight font-bold tracking-tight text-slate-950 md:text-4xl"
                        >
                            Everything you need for<br />
                            <span class="font-semibold text-slate-900"
                                >Better health—in one place.</span
                            >
                        </h2>
                        <p
                            class="mt-3 max-w-xl text-xs leading-relaxed text-slate-600 md:text-sm"
                        >
                            Pelayanan kesehatan terintegrasi tanpa alur
                            berbelit. Kami mengutamakan ketepatan waktu dengan
                            sistem reservasi antrean digital serta resep instan.
                        </p>
                    </div>

                    <!-- Clean Circular Nav Buttons -->
                    <div class="flex shrink-0 items-center gap-2">
                        <div
                            class="flex size-10 items-center justify-center rounded-full border border-slate-200/80 bg-white/80 text-slate-400 backdrop-blur-sm"
                        >
                            <ChevronLeft class="size-4" />
                        </div>
                        <div
                            class="flex size-10 items-center justify-center rounded-full border border-slate-200/80 bg-white/80 text-slate-700 backdrop-blur-sm"
                        >
                            <ChevronRight class="size-4" />
                        </div>
                    </div>
                </div>

                <!-- 4 Feature Cards -->
                <div
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Card 1 -->
                    <div
                        class="group relative flex h-88 flex-col justify-end overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100 p-5 shadow-2xs"
                    >
                        <img
                            src="/images/doctor-illustration.jpg"
                            alt="Comprehensive Care"
                            class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-103"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"
                        ></div>
                        <div class="relative z-10 space-y-1 text-white">
                            <h3
                                class="text-sm leading-snug font-bold text-white"
                            >
                                Pelayanan Menyeluruh
                            </h3>
                            <p
                                class="text-[11px] leading-relaxed text-slate-300"
                            >
                                Dari pemeriksaan awal hingga rujukan spesialis
                                lanjutan dalam satu rekam medis terpadu.
                            </p>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div
                        class="group relative flex h-88 flex-col justify-end overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100 p-5 shadow-2xs"
                    >
                        <img
                            :src="heroBannerImage"
                            alt="Collaborative Doctors"
                            class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-103"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"
                        ></div>
                        <div class="relative z-10 space-y-1 text-white">
                            <h3
                                class="text-sm leading-snug font-bold text-white"
                            >
                                Dokter Kolaboratif
                            </h3>
                            <p
                                class="text-[11px] leading-relaxed text-slate-300"
                            >
                                Diskusi antar dokter spesialis untuk menentukan
                                rencana terapi terbaik bagi kesembuhan pasien.
                            </p>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div
                        class="group relative flex h-88 flex-col justify-end overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100 p-5 shadow-2xs"
                    >
                        <img
                            src="/images/doctor-illustration.jpg"
                            alt="Modern Diagnostics"
                            class="absolute inset-0 size-full object-cover object-right transition-transform duration-500 group-hover:scale-103"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"
                        ></div>
                        <div class="relative z-10 space-y-1 text-white">
                            <h3
                                class="text-sm leading-snug font-bold text-white"
                            >
                                Apotek & Resep Terpadu
                            </h3>
                            <p
                                class="text-[11px] leading-relaxed text-slate-300"
                            >
                                Peresepan digital otomatis langsung ke apotek
                                internal, siap diambil tanpa antre ganda.
                            </p>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div
                        class="group relative flex h-88 flex-col justify-end overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100 p-5 shadow-2xs"
                    >
                        <img
                            :src="heroBannerImage"
                            alt="Convenient Scheduling"
                            class="absolute inset-0 size-full object-cover object-left transition-transform duration-500 group-hover:scale-103"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"
                        ></div>
                        <div class="relative z-10 space-y-1 text-white">
                            <h3
                                class="text-sm leading-snug font-bold text-white"
                            >
                                Reservasi Antrean Praktis
                            </h3>
                            <p
                                class="text-[11px] leading-relaxed text-slate-300"
                            >
                                Ambil nomor secara online, pilih sesi pagi/sore,
                                dan pantau posisi antrean real-time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. SPECIALTIES SECTION (Pill/Card Minimalis Bersih) -->
        <section id="spesialisasi" class="relative z-10 py-20">
            <div class="mx-auto max-w-[1240px] px-6">
                <!-- Header with Action -->
                <div
                    class="mb-10 flex flex-col justify-between gap-6 md:flex-row md:items-end"
                >
                    <div>
                        <h2
                            class="text-2xl font-bold tracking-tight text-slate-950 md:text-3xl"
                        >
                            Expert care across<br />
                            <span class="text-slate-800"
                                >Every major specialty</span
                            >
                        </h2>
                        <p class="mt-2 text-xs text-slate-600 md:text-sm">
                            Pilihan poliklinik dan dokter spesialis
                            berpengalaman untuk penanganan medis tepat sasaran.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link
                            href="/queue"
                            class="inline-flex h-10 items-center gap-1.5 rounded-full bg-slate-900 px-5 text-xs font-medium text-white shadow-2xs transition-all hover:bg-slate-800"
                        >
                            <span>Daftar Sesuai Poli</span>
                            <ArrowRight class="size-3" />
                        </Link>
                    </div>
                </div>

                <!-- Clean Card Grid for Specialties -->
                <div
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4"
                >
                    <div
                        v-for="spec in specialties"
                        :key="spec"
                        class="group flex items-center gap-3.5 rounded-xl border border-slate-200/80 bg-white/80 p-4 shadow-2xs backdrop-blur-md transition-all hover:border-slate-400 hover:shadow-xs"
                    >
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-800 transition-colors group-hover:bg-slate-900 group-hover:text-white"
                        >
                            <component
                                :is="getSpecialtyIcon(spec)"
                                class="size-4.5"
                            />
                        </div>
                        <div
                            class="text-xs leading-tight font-semibold text-slate-900 sm:text-sm"
                        >
                            {{ spec }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. DOCTORS PROFILE SECTION (Dynamic from Database dengan Ringkasan Jadwal) -->
        <section id="dokter" class="relative z-10 py-24">
            <div class="mx-auto max-w-[1240px] px-6">
                <div
                    class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end"
                >
                    <div>
                        <span
                            class="mb-1 block text-[11px] font-bold tracking-wider text-slate-500 uppercase"
                        >
                            Tenaga Medis Profesional
                        </span>
                        <h2
                            class="text-3xl font-bold tracking-tight text-slate-950 md:text-4xl"
                        >
                            {{
                                landing.doctors_section_title ||
                                'Tim Dokter Klinik'
                            }}
                        </h2>
                        <p
                            class="mt-2 max-w-xl text-xs text-slate-600 md:text-sm"
                        >
                            {{
                                landing.doctors_section_subtitle ||
                                'Daftar dokter yang berpraktik dengan kualifikasi STR resmi dan jadwal yang teratur.'
                            }}
                        </p>
                    </div>

                    <Link
                        href="/queue"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-900 transition-colors hover:text-indigo-600"
                    >
                        <span>Pilih Dokter & Ambil Slot</span>
                        <ArrowRight class="size-3.5" />
                    </Link>
                </div>

                <!-- Doctors Card Grid -->
                <div
                    v-if="doctors.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="doctor in doctors"
                        :key="doctor.doctor_id"
                        class="group flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white/85 p-5 shadow-2xs backdrop-blur-md transition-all hover:border-slate-400"
                    >
                        <div>
                            <!-- Header: Foto Profil & Info -->
                            <div class="mb-4 flex items-start gap-3.5">
                                <div
                                    class="size-16 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
                                >
                                    <img
                                        v-if="
                                            doctor.photo_url ||
                                            doctor.photo_path
                                        "
                                        :src="
                                            doctor.photo_url ||
                                            `/storage/${doctor.photo_path}`
                                        "
                                        :alt="doctor.doctor_name"
                                        class="size-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex size-full items-center justify-center bg-slate-50 text-slate-400"
                                    >
                                        <Stethoscope
                                            class="size-7 text-slate-400"
                                        />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <span
                                        v-if="doctor.queue_prefix"
                                        class="inline-flex items-center rounded border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-800"
                                    >
                                        Poli: {{ doctor.queue_prefix }}
                                    </span>
                                    <h3
                                        class="text-sm leading-snug font-bold text-slate-900"
                                    >
                                        {{ doctor.doctor_name }}
                                    </h3>
                                    <p
                                        class="flex items-center gap-1 text-xs font-medium text-slate-600"
                                    >
                                        <Stethoscope
                                            class="size-3 text-indigo-600"
                                        />
                                        {{ doctor.specialization }}
                                    </p>
                                    <p
                                        v-if="doctor.str_number"
                                        class="font-mono text-[10px] text-slate-400"
                                    >
                                        STR: {{ doctor.str_number }}
                                    </p>
                                </div>
                            </div>

                            <!-- Ringkasan Jadwal Dokter -->
                            <div
                                class="mt-3 space-y-2 border-t border-slate-100 pt-3"
                            >
                                <div
                                    class="flex items-center gap-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    <Calendar class="size-3 text-slate-500" />
                                    <span>Jadwal Praktik Aktif:</span>
                                </div>
                                <div
                                    v-if="
                                        doctor.practice_schedules &&
                                        doctor.practice_schedules.length > 0
                                    "
                                    class="space-y-1"
                                >
                                    <div
                                        v-for="sched in doctor.practice_schedules"
                                        :key="sched.schedule_id"
                                        class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50/80 px-2.5 py-1 text-xs text-slate-700"
                                    >
                                        <span
                                            class="font-medium text-slate-900"
                                        >
                                            {{ formatDay(sched.day) }} ({{
                                                formatSession(sched.session)
                                            }})
                                        </span>
                                        <span
                                            class="font-mono text-[11px] text-slate-500"
                                        >
                                            {{
                                                formatTime(sched.opening_hours)
                                            }}
                                            -
                                            {{
                                                formatTime(sched.closing_hours)
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="py-1 text-xs text-slate-400 italic"
                                >
                                    Jadwal praktik belum diperbarui.
                                </div>
                            </div>
                        </div>

                        <!-- Card CTA -->
                        <div class="mt-5 pt-3">
                            <Link
                                href="/queue"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-900 transition-colors hover:bg-slate-900 hover:text-white"
                            >
                                <span>Pilih Jadwal & Daftar</span>
                                <ArrowRight class="size-3" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="rounded-2xl border border-dashed border-slate-300 bg-white/80 py-16 text-center backdrop-blur-md"
                >
                    <Stethoscope class="mx-auto mb-3 size-10 text-slate-400" />
                    <p class="text-sm font-medium text-slate-600">
                        Belum ada data dokter aktif.
                    </p>
                </div>
            </div>
        </section>

        <!-- 6. LOCATION & EMERGENCY CALLOUT (Identik dengan Gambar 4) -->
        <section id="lokasi" class="relative z-10 py-20">
            <div class="mx-auto max-w-[1240px] px-6">
                <div
                    class="flex flex-col items-center justify-between gap-8 rounded-3xl border border-white/5 bg-[#090b10] p-8 text-white shadow-sm md:flex-row md:p-12"
                >
                    <div class="max-w-xl space-y-3 text-center md:text-left">
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-slate-300"
                        >
                            <span
                                class="size-1.5 rounded-full bg-emerald-400"
                            ></span>
                            <span>{{
                                landing.emergency_contact ||
                                'Hubungi Hotline Darurat: (021) 555-0123'
                            }}</span>
                        </div>
                        <h3
                            class="text-2xl leading-tight font-bold tracking-tight text-white md:text-3xl"
                        >
                            Siap Melayani Kebutuhan Kesehatan Anda & Keluarga
                        </h3>
                        <p
                            class="text-xs leading-relaxed font-normal text-slate-400"
                        >
                            Kunjungi klinik kami di alamat tertera atau hubungi
                            kontak layanan untuk informasi registrasi pasien dan
                            jadwal dokter hari ini.
                        </p>
                        <div
                            class="flex flex-col items-center gap-5 pt-2 text-xs font-medium text-slate-300 sm:flex-row"
                        >
                            <div class="flex items-center gap-2">
                                <MapPin class="size-3.5 text-slate-400" />
                                <span>{{
                                    clinic.clinic_address ||
                                    'Jl. Merdeka Sejahtera No. 88, Bandung'
                                }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Phone class="size-3.5 text-slate-400" />
                                <span>{{
                                    clinic.clinic_phone || '(022) 765-4321'
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex w-full shrink-0 flex-col gap-2.5 sm:w-auto"
                    >
                        <Link
                            href="/queue"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-white px-7 text-xs font-semibold tracking-tight text-slate-950 shadow-sm transition-all hover:bg-slate-100"
                        >
                            <Ticket class="size-3.5" />
                            <span>Daftar Antrean Online</span>
                        </Link>
                        <a
                            :href="`tel:${clinic.clinic_phone || ''}`"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-full border border-white/20 bg-white/5 px-7 text-xs font-medium tracking-tight text-white transition-all hover:bg-white/10"
                        >
                            <Phone class="size-3.5 text-slate-300" />
                            <span>Hubungi Resepsionis</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. FOOTER -->
        <footer
            class="relative z-10 border-t border-slate-200/80 bg-white/80 py-10 text-xs text-slate-500 backdrop-blur-md"
        >
            <div
                class="mx-auto flex max-w-[1240px] flex-col items-center justify-between gap-4 px-6 sm:flex-row"
            >
                <div class="flex items-center gap-2.5">
                    <span
                        class="font-bold tracking-wider text-slate-900 uppercase"
                    >
                        {{ clinic.clinic_name || 'Klinik Pratama Sehat' }}
                    </span>
                    <span
                        >&copy; {{ new Date().getFullYear() }} All rights
                        reserved.</span
                    >
                </div>

                <div class="flex items-center gap-6 text-slate-600">
                    <a
                        href="#layanan"
                        class="transition-colors hover:text-slate-900"
                        >Layanan</a
                    >
                    <a
                        href="#spesialisasi"
                        class="transition-colors hover:text-slate-900"
                        >Spesialisasi</a
                    >
                    <a
                        href="#dokter"
                        class="transition-colors hover:text-slate-900"
                        >Dokter</a
                    >
                    <Link
                        href="/login"
                        class="font-semibold text-slate-900 hover:underline"
                    >
                        Portal Karyawan
                    </Link>
                </div>
            </div>
        </footer>
    </div>
</template>
