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
    <div class="min-h-screen relative font-sans select-none bg-[#f2f6fc] text-slate-900 antialiased overflow-x-hidden selection:bg-indigo-600 selection:text-white">
        
        <!-- Background Ambient Mesh Silky Gradient Wave (Identik Halaman Login) -->
        <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
            <!-- Sky Blue & Lavender Soft Background Radial -->
            <div class="absolute -top-1/4 left-0 w-full h-[600px] bg-radial from-[#e0efff]/80 via-[#edf4fd]/60 to-transparent blur-3xl pointer-events-none" />
            
            <!-- Gelombang Halus Biru Keunguan di Tengah Menyeberang ke Bawah (Wave Flow) -->
            <div class="absolute top-[25%] -left-[10%] w-[120%] h-[55%] rounded-[100%] bg-gradient-to-r from-blue-300/30 via-indigo-400/25 to-purple-400/20 blur-[100px] transform -rotate-[7deg]" />
            <div class="absolute top-[48%] -left-[5%] w-[110%] h-[40%] rounded-[100%] bg-gradient-to-r from-blue-400/20 via-indigo-500/15 to-blue-300/10 blur-[80px] transform -rotate-[4deg]" />
            
            <!-- Pita Gelombang Halus Vektor (Subtle Elegant Ribbon Curve) -->
            <svg class="absolute inset-0 w-full h-full opacity-60 pointer-events-none" viewBox="0 0 1440 900" fill="none" preserveAspectRatio="none">
                <path d="M-100,560 C320,680 580,480 920,530 C1200,570 1350,460 1550,500 L1550,950 L-100,950 Z" fill="url(#wave-gradient-1)" opacity="0.3" />
                <path d="M-50,610 C380,720 700,520 1020,580 C1280,630 1420,530 1550,560 L1550,950 L-50,950 Z" fill="url(#wave-gradient-2)" opacity="0.4" />
                <defs>
                    <linearGradient id="wave-gradient-1" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#bfdbfe" stop-opacity="0.8" />
                        <stop offset="50%" stop-color="#c7d2fe" stop-opacity="0.6" />
                        <stop offset="100%" stop-color="#ddd6fe" stop-opacity="0.7" />
                    </linearGradient>
                    <linearGradient id="wave-gradient-2" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#93c5fd" stop-opacity="0.5" />
                        <stop offset="60%" stop-color="#818cf8" stop-opacity="0.4" />
                        <stop offset="100%" stop-color="#a78bfa" stop-opacity="0.5" />
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <!-- 1. FLOATING NAVIGATION BAR (Glass Pill Island di Bagian Atas) -->
        <div class="fixed top-5 inset-x-0 z-50 flex justify-center px-4 pointer-events-none">
            <header class="w-full max-w-[1180px] h-14 rounded-full bg-white/80 backdrop-blur-xl border border-white/80 shadow-[0_8px_30px_rgb(0,0,0,0.06)] px-5 flex items-center justify-between pointer-events-auto transition-all">
                <!-- Brand Title / Logo -->
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="size-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs tracking-wider shadow-2xs group-hover:bg-indigo-600 transition-colors">
                        <img
                            v-if="clinic.clinic_logo_url"
                            :src="clinic.clinic_logo_url"
                            :alt="clinic.clinic_name"
                            class="size-full object-contain p-1 rounded-full"
                        />
                        <span v-else class="text-[10px] uppercase font-bold font-mono">
                            {{ (clinic.clinic_name || 'KLN').substring(0, 3) }}
                        </span>
                    </div>
                    <span class="font-bold text-xs sm:text-sm tracking-tight text-slate-900 uppercase">
                        {{ clinic.clinic_name || 'Klinik Sehat Medika' }}
                    </span>
                </Link>

                <!-- Center Nav Links -->
                <nav class="hidden md:flex items-center gap-7 text-xs font-medium text-slate-600">
                    <a href="#layanan" class="hover:text-slate-950 transition-colors">Layanan</a>
                    <a href="#spesialisasi" class="hover:text-slate-950 transition-colors">Spesialisasi</a>
                    <a href="#dokter" class="hover:text-slate-950 transition-colors">Dokter</a>
                    <a href="#lokasi" class="hover:text-slate-950 transition-colors">Kontak</a>
                </nav>

                <!-- Right Action Buttons -->
                <div class="flex items-center gap-2">
                    <template v-if="$page.props.auth?.user">
                        <Link
                            href="/dashboard"
                            class="inline-flex items-center gap-1.5 h-8 px-4 rounded-full bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 transition-all shadow-2xs"
                        >
                            <LayoutDashboard class="size-3.5" />
                            <span>Dashboard</span>
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="hidden sm:inline-flex items-center gap-1.5 h-8 px-3.5 rounded-full border border-slate-200/80 bg-white/70 text-slate-700 text-xs font-medium hover:bg-white transition-all"
                        >
                            <LogIn class="size-3 text-slate-500" />
                            <span>Masuk</span>
                        </Link>
                        <Link
                            href="/queue"
                            class="inline-flex items-center gap-1.5 h-8 px-4.5 rounded-full bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 transition-all shadow-2xs"
                        >
                            <span>Daftar Antrean</span>
                            <ArrowRight class="size-3" />
                        </Link>
                    </template>
                </div>
            </header>
        </div>

        <!-- 2. HERO SECTION: Gambar Terang & Jelas (Tanpa Card-Card Statistik, Tipografi Editorial Elegan Persis Synora) -->
        <section class="relative min-h-[640px] lg:min-h-[720px] flex items-center pt-28 pb-16 overflow-hidden">
            <!-- Background Image Asli (Terang & Jelas) -->
            <div class="absolute inset-0 z-0">
                <img
                    :src="heroBannerImage"
                    alt="Klinik Banner"
                    class="size-full object-cover object-center filter brightness-100 contrast-[1.02]"
                />
                <!-- Subtle Gradient Mask: Hanya di bagian teks kiri agar tulisan tetap terbaca jelas -->
                <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/80 to-transparent sm:w-2/3 lg:w-1/2"></div>
            </div>

            <div class="max-w-[1240px] mx-auto px-6 w-full relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <!-- Left Hero Content (Editorial Typography) -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="text-[11px] font-bold tracking-[0.2em] text-slate-600 uppercase">
                            {{ landing.hero_badge || 'MULTI-SPECIALTY MEDICAL CLINIC' }}
                        </div>

                        <!-- Big Headline -->
                        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-[-0.03em] leading-[1.02] text-slate-950 uppercase max-w-2xl text-balance">
                            {{ landing.hero_title || 'HEALTHCARE DESIGNED AROUND YOU' }}
                        </h1>

                        <!-- Buttons Pill (Obsidian & Ghost Outline) -->
                        <div class="pt-2 flex flex-wrap items-center gap-3">
                            <Link
                                href="/queue"
                                class="inline-flex items-center gap-2 h-11 px-6 rounded-full bg-slate-900 text-white font-medium text-xs tracking-tight shadow-md hover:bg-slate-800 hover:scale-[1.01] active:scale-[0.99] transition-all"
                            >
                                <Ticket class="size-3.5" />
                                <span>{{ landing.cta_primary_text || 'Ambil Nomor Antrean' }}</span>
                            </Link>
                            <a
                                href="#dokter"
                                class="inline-flex items-center gap-2 h-11 px-6 rounded-full bg-white/90 border border-slate-300 text-slate-800 font-medium text-xs tracking-tight hover:bg-white transition-all shadow-2xs"
                            >
                                <Stethoscope class="size-3.5 text-indigo-600" />
                                <span>{{ landing.cta_secondary_text || 'Cari Dokter' }}</span>
                            </a>
                        </div>

                        <!-- Bottom Editorial Note -->
                        <div class="pt-6 max-w-md">
                            <p class="text-xs text-slate-600 leading-relaxed font-normal">
                                {{ landing.hero_subtitle || 'Layanan rawat jalan terpadu dengan dokter umum dan spesialis profesional. Nikmati sistem pendaftaran dan pemantauan antrean tanpa perlu menghabiskan waktu menunggu berlama-lama di ruang tunggu klinik.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Right Stats Column (CLEAN MINIMALIST TANPA KARTU/CARD, Persis Seperti Referensi Synora) -->
                    <div class="lg:col-span-5 lg:pl-16 flex flex-col gap-8 justify-center text-right sm:text-right">
                        <!-- Stat 1 -->
                        <div class="space-y-0.5">
                            <div class="text-4xl sm:text-5xl font-extrabold text-slate-950 tracking-tight">
                                {{ (stats?.total_patients ?? 0) > 0 ? (stats?.total_patients ?? 0) + '+' : '20K+' }}
                            </div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                Pasien Terdaftar & Terlayani
                            </div>
                        </div>

                        <!-- Stat 2 -->
                        <div class="space-y-0.5">
                            <div class="text-4xl sm:text-5xl font-extrabold text-slate-950 tracking-tight">
                                {{ doctors.length > 0 ? doctors.length : '30+' }}+
                            </div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                Dokter Bersertifikasi & Aktif
                            </div>
                        </div>

                        <!-- Stat 3 -->
                        <div class="space-y-0.5">
                            <div class="text-4xl sm:text-5xl font-extrabold text-slate-950 tracking-tight">
                                {{ specialties.length > 0 ? specialties.length : '18' }}
                            </div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                Poli & Bidang Spesialisasi
                            </div>
                        </div>

                        <!-- Stat 4 -->
                        <div class="space-y-0.5">
                            <div class="text-4xl sm:text-5xl font-extrabold text-slate-950 tracking-tight">
                                4.9/5
                            </div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                Tingkat Kepuasan Pasien
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. SERVICES SECTION (Foto Minimalis dengan Overlay Gradien Halus) -->
        <section id="layanan" class="py-24 relative z-10">
            <div class="max-w-[1240px] mx-auto px-6">
                <!-- Section Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div class="max-w-2xl">
                        <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-950 leading-tight">
                            Everything you need for<br />
                            <span class="text-slate-900 font-semibold">Better health—in one place.</span>
                        </h2>
                        <p class="mt-3 text-xs md:text-sm text-slate-600 leading-relaxed max-w-xl">
                            Pelayanan kesehatan terintegrasi tanpa alur berbelit. Kami mengutamakan ketepatan waktu dengan sistem reservasi antrean digital serta resep instan.
                        </p>
                    </div>

                    <!-- Clean Circular Nav Buttons -->
                    <div class="flex items-center gap-2 shrink-0">
                        <div class="size-10 rounded-full border border-slate-200/80 bg-white/80 backdrop-blur-sm flex items-center justify-center text-slate-400">
                            <ChevronLeft class="size-4" />
                        </div>
                        <div class="size-10 rounded-full border border-slate-200/80 bg-white/80 backdrop-blur-sm flex items-center justify-center text-slate-700">
                            <ChevronRight class="size-4" />
                        </div>
                    </div>
                </div>

                <!-- 4 Feature Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- Card 1 -->
                    <div class="relative h-88 rounded-2xl overflow-hidden border border-slate-200/80 shadow-2xs flex flex-col justify-end p-5 group bg-slate-100">
                        <img
                            src="/images/doctor-illustration.jpg"
                            alt="Comprehensive Care"
                            class="absolute inset-0 size-full object-cover group-hover:scale-103 transition-transform duration-500"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                        <div class="relative z-10 space-y-1 text-white">
                            <h3 class="text-sm font-bold text-white leading-snug">Pelayanan Menyeluruh</h3>
                            <p class="text-[11px] text-slate-300 leading-relaxed">
                                Dari pemeriksaan awal hingga rujukan spesialis lanjutan dalam satu rekam medis terpadu.
                            </p>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="relative h-88 rounded-2xl overflow-hidden border border-slate-200/80 shadow-2xs flex flex-col justify-end p-5 group bg-slate-100">
                        <img
                            :src="heroBannerImage"
                            alt="Collaborative Doctors"
                            class="absolute inset-0 size-full object-cover group-hover:scale-103 transition-transform duration-500"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                        <div class="relative z-10 space-y-1 text-white">
                            <h3 class="text-sm font-bold text-white leading-snug">Dokter Kolaboratif</h3>
                            <p class="text-[11px] text-slate-300 leading-relaxed">
                                Diskusi antar dokter spesialis untuk menentukan rencana terapi terbaik bagi kesembuhan pasien.
                            </p>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="relative h-88 rounded-2xl overflow-hidden border border-slate-200/80 shadow-2xs flex flex-col justify-end p-5 group bg-slate-100">
                        <img
                            src="/images/doctor-illustration.jpg"
                            alt="Modern Diagnostics"
                            class="absolute inset-0 size-full object-cover object-right group-hover:scale-103 transition-transform duration-500"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                        <div class="relative z-10 space-y-1 text-white">
                            <h3 class="text-sm font-bold text-white leading-snug">Apotek & Resep Terpadu</h3>
                            <p class="text-[11px] text-slate-300 leading-relaxed">
                                Peresepan digital otomatis langsung ke apotek internal, siap diambil tanpa antre ganda.
                            </p>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="relative h-88 rounded-2xl overflow-hidden border border-slate-200/80 shadow-2xs flex flex-col justify-end p-5 group bg-slate-100">
                        <img
                            :src="heroBannerImage"
                            alt="Convenient Scheduling"
                            class="absolute inset-0 size-full object-cover object-left group-hover:scale-103 transition-transform duration-500"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                        <div class="relative z-10 space-y-1 text-white">
                            <h3 class="text-sm font-bold text-white leading-snug">Reservasi Antrean Praktis</h3>
                            <p class="text-[11px] text-slate-300 leading-relaxed">
                                Ambil nomor secara online, pilih sesi pagi/sore, dan pantau posisi antrean real-time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. SPECIALTIES SECTION (Pill/Card Minimalis Bersih) -->
        <section id="spesialisasi" class="py-20 relative z-10">
            <div class="max-w-[1240px] mx-auto px-6">
                <!-- Header with Action -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-950">
                            Expert care across<br />
                            <span class="text-slate-800">Every major specialty</span>
                        </h2>
                        <p class="mt-2 text-xs md:text-sm text-slate-600">
                            Pilihan poliklinik dan dokter spesialis berpengalaman untuk penanganan medis tepat sasaran.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link
                            href="/queue"
                            class="inline-flex items-center gap-1.5 h-10 px-5 rounded-full bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 transition-all shadow-2xs"
                        >
                            <span>Daftar Sesuai Poli</span>
                            <ArrowRight class="size-3" />
                        </Link>
                    </div>
                </div>

                <!-- Clean Card Grid for Specialties -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <div
                        v-for="spec in specialties"
                        :key="spec"
                        class="flex items-center gap-3.5 p-4 rounded-xl bg-white/80 backdrop-blur-md border border-slate-200/80 shadow-2xs hover:border-slate-400 hover:shadow-xs transition-all group"
                    >
                        <div class="size-9 rounded-lg bg-slate-100 flex items-center justify-center text-slate-800 shrink-0 group-hover:bg-slate-900 group-hover:text-white transition-colors">
                            <component :is="getSpecialtyIcon(spec)" class="size-4.5" />
                        </div>
                        <div class="font-semibold text-xs sm:text-sm text-slate-900 leading-tight">
                            {{ spec }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. DOCTORS PROFILE SECTION (Dynamic from Database dengan Ringkasan Jadwal) -->
        <section id="dokter" class="py-24 relative z-10">
            <div class="max-w-[1240px] mx-auto px-6">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1 block">
                            Tenaga Medis Profesional
                        </span>
                        <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-950">
                            {{ landing.doctors_section_title || 'Tim Dokter Klinik' }}
                        </h2>
                        <p class="mt-2 text-xs md:text-sm text-slate-600 max-w-xl">
                            {{ landing.doctors_section_subtitle || 'Daftar dokter yang berpraktik dengan kualifikasi STR resmi dan jadwal yang teratur.' }}
                        </p>
                    </div>

                    <Link
                        href="/queue"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-900 hover:text-indigo-600 transition-colors"
                    >
                        <span>Pilih Dokter & Ambil Slot</span>
                        <ArrowRight class="size-3.5" />
                    </Link>
                </div>

                <!-- Doctors Card Grid -->
                <div v-if="doctors.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="doctor in doctors"
                        :key="doctor.doctor_id"
                        class="group rounded-2xl bg-white/85 backdrop-blur-md border border-slate-200/90 p-5 shadow-2xs hover:border-slate-400 transition-all flex flex-col justify-between"
                    >
                        <div>
                            <!-- Header: Foto Profil & Info -->
                            <div class="flex items-start gap-3.5 mb-4">
                                <div class="size-16 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0">
                                    <img
                                        v-if="doctor.photo_url || doctor.photo_path"
                                        :src="doctor.photo_url || `/storage/${doctor.photo_path}`"
                                        :alt="doctor.doctor_name"
                                        class="size-full object-cover"
                                    />
                                    <div v-else class="size-full flex items-center justify-center text-slate-400 bg-slate-50">
                                        <Stethoscope class="size-7 text-slate-400" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <span
                                        v-if="doctor.queue_prefix"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200"
                                    >
                                        Poli: {{ doctor.queue_prefix }}
                                    </span>
                                    <h3 class="text-sm font-bold text-slate-900 leading-snug">
                                        {{ doctor.doctor_name }}
                                    </h3>
                                    <p class="text-xs text-slate-600 font-medium flex items-center gap-1">
                                        <Stethoscope class="size-3 text-indigo-600" />
                                        {{ doctor.specialization }}
                                    </p>
                                    <p v-if="doctor.str_number" class="text-[10px] text-slate-400 font-mono">
                                        STR: {{ doctor.str_number }}
                                    </p>
                                </div>
                            </div>

                            <!-- Ringkasan Jadwal Dokter -->
                            <div class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <Calendar class="size-3 text-slate-500" />
                                    <span>Jadwal Praktik Aktif:</span>
                                </div>
                                <div
                                    v-if="doctor.practice_schedules && doctor.practice_schedules.length > 0"
                                    class="space-y-1"
                                >
                                    <div
                                        v-for="sched in doctor.practice_schedules"
                                        :key="sched.schedule_id"
                                        class="flex items-center justify-between text-xs py-1 px-2.5 rounded-lg bg-slate-50/80 border border-slate-100 text-slate-700"
                                    >
                                        <span class="font-medium text-slate-900">
                                            {{ formatDay(sched.day) }} ({{ formatSession(sched.session) }})
                                        </span>
                                        <span class="font-mono text-[11px] text-slate-500">
                                            {{ formatTime(sched.opening_hours) }} - {{ formatTime(sched.closing_hours) }}
                                        </span>
                                    </div>
                                </div>
                                <div v-else class="text-xs text-slate-400 italic py-1">
                                    Jadwal praktik belum diperbarui.
                                </div>
                            </div>
                        </div>

                        <!-- Card CTA -->
                        <div class="mt-5 pt-3">
                            <Link
                                href="/queue"
                                class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl border border-slate-200 text-xs font-semibold text-slate-900 hover:bg-slate-900 hover:text-white transition-colors"
                            >
                                <span>Pilih Jadwal & Daftar</span>
                                <ArrowRight class="size-3" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16 bg-white/80 backdrop-blur-md rounded-2xl border border-dashed border-slate-300">
                    <Stethoscope class="size-10 text-slate-400 mx-auto mb-3" />
                    <p class="text-sm font-medium text-slate-600">Belum ada data dokter aktif.</p>
                </div>
            </div>
        </section>

        <!-- 6. LOCATION & EMERGENCY CALLOUT (Identik dengan Gambar 4) -->
        <section id="lokasi" class="py-20 relative z-10">
            <div class="max-w-[1240px] mx-auto px-6">
                <div class="rounded-3xl bg-[#090b10] text-white p-8 md:p-12 shadow-sm flex flex-col md:flex-row items-center justify-between gap-8 border border-white/5">
                    <div class="space-y-3 max-w-xl text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-slate-300 text-xs font-medium">
                            <span class="size-1.5 rounded-full bg-emerald-400"></span>
                            <span>{{ landing.emergency_contact || 'Hubungi Hotline Darurat: (021) 555-0123' }}</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-bold tracking-tight text-white leading-tight">
                            Siap Melayani Kebutuhan Kesehatan Anda & Keluarga
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed font-normal">
                            Kunjungi klinik kami di alamat tertera atau hubungi kontak layanan untuk informasi registrasi pasien dan jadwal dokter hari ini.
                        </p>
                        <div class="pt-2 flex flex-col sm:flex-row items-center gap-5 text-xs text-slate-300 font-medium">
                            <div class="flex items-center gap-2">
                                <MapPin class="size-3.5 text-slate-400" />
                                <span>{{ clinic.clinic_address || 'Jl. Merdeka Sejahtera No. 88, Bandung' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Phone class="size-3.5 text-slate-400" />
                                <span>{{ clinic.clinic_phone || '(022) 765-4321' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 flex flex-col gap-2.5 w-full sm:w-auto">
                        <Link
                            href="/queue"
                            class="inline-flex items-center justify-center gap-2 h-11 px-7 rounded-full bg-white text-slate-950 font-semibold text-xs tracking-tight shadow-sm hover:bg-slate-100 transition-all"
                        >
                            <Ticket class="size-3.5" />
                            <span>Daftar Antrean Online</span>
                        </Link>
                        <a
                            :href="`tel:${clinic.clinic_phone || ''}`"
                            class="inline-flex items-center justify-center gap-2 h-11 px-7 rounded-full border border-white/20 bg-white/5 text-white font-medium text-xs tracking-tight hover:bg-white/10 transition-all"
                        >
                            <Phone class="size-3.5 text-slate-300" />
                            <span>Hubungi Resepsionis</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. FOOTER -->
        <footer class="py-10 bg-white/80 backdrop-blur-md border-t border-slate-200/80 text-xs text-slate-500 relative z-10">
            <div class="max-w-[1240px] mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <span class="font-bold text-slate-900 uppercase tracking-wider">
                        {{ clinic.clinic_name || 'Klinik Pratama Sehat' }}
                    </span>
                    <span>&copy; {{ new Date().getFullYear() }} All rights reserved.</span>
                </div>

                <div class="flex items-center gap-6 text-slate-600">
                    <a href="#layanan" class="hover:text-slate-900 transition-colors">Layanan</a>
                    <a href="#spesialisasi" class="hover:text-slate-900 transition-colors">Spesialisasi</a>
                    <a href="#dokter" class="hover:text-slate-900 transition-colors">Dokter</a>
                    <Link href="/login" class="text-slate-900 font-semibold hover:underline">
                        Portal Karyawan
                    </Link>
                </div>
            </div>
        </footer>
    </div>
</template>
