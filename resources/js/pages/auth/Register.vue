<script setup lang="ts">
import { computed, ref } from 'vue';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

const page = usePage();
const clinicInfo = computed(() => {
    return (page.props as any).clinic || {
        clinic_name: 'KLINIK SEHAT',
    };
});

const agreeTerms = ref(true);
</script>

<template>
    <Head title="Daftar Akun" />

    <!-- Container Full Viewport Lock (h-screen w-screen overflow-hidden) Pas Tanpa Scroll -->
    <div class="relative h-screen w-screen overflow-hidden font-sans select-none bg-[#f2f6fc] text-slate-900 flex flex-col justify-between p-6 sm:p-10 md:p-12 box-border">
        
        <!-- Background Ambient Smooth Silky Wave & Mesh Gradasi Sesuai Referensi -->
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
            <!-- Sky Blue & Lavender Soft Background Gradients -->
            <div class="absolute -top-1/4 left-0 w-full h-full bg-radial from-[#e0efff]/80 via-[#edf4fd]/60 to-transparent blur-3xl pointer-events-none" />
            
            <!-- Gelombang Halus Biru Keunguan di Tengah Menyeberang ke Bawah (Wave Flow) -->
            <div class="absolute top-[35%] -left-[10%] w-[120%] h-[55%] rounded-[100%] bg-gradient-to-r from-blue-300/35 via-indigo-400/30 to-purple-400/25 blur-[90px] transform -rotate-[7deg]" />
            <div class="absolute top-[48%] -left-[5%] w-[110%] h-[40%] rounded-[100%] bg-gradient-to-r from-blue-400/25 via-indigo-500/20 to-blue-300/15 blur-[70px] transform -rotate-[4deg]" />
            
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

        <!-- 1. Header Pojok Kiri Atas: Logo Sederhana & Modern Sesuai Referensi -->
        <header class="relative z-10 flex items-center justify-between shrink-0">
            <Link href="/" class="flex items-center gap-2.5 group">
                <!-- Simbol Ikon Neovos Style -->
                <div class="relative size-8 flex items-center justify-center">
                    <span class="absolute text-blue-500 font-bold text-lg select-none top-0 -left-1">✦</span>
                    <div class="size-6 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-xs">
                        <div class="size-2 rounded-full bg-white"></div>
                    </div>
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-800 lowercase">
                    {{ clinicInfo.clinic_name.toLowerCase().replace(/\s+/g, '') }}
                </span>
            </Link>
        </header>

        <!-- 2. Main Center Body: Pas Layar (Sisi Kiri Typo + Sisi Kanan Floating Glass Card) -->
        <main class="relative z-10 flex-1 flex items-center justify-between w-full max-w-6xl mx-auto my-auto px-4 lg:px-8">
            <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                <!-- Sisi Kiri (7 Kolom): Elegan, Bersih, Floating Chat Pill Halus Transparan Ala Gambar Referensi -->
                <div class="hidden lg:flex lg:col-span-7 flex-col justify-center items-start pl-6 select-none relative">
                    <!-- Chat Bubbles Transparan Bertumpuk Halus di Atas Teks Logo -->
                    <div class="flex flex-col items-start gap-1.5 mb-5 ml-14">
                        <div class="h-3 w-16 bg-white/40 backdrop-blur-md rounded-full shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-white/60"></div>
                        <div class="bg-white/55 backdrop-blur-md border border-white/80 text-slate-600/90 text-[11px] font-medium py-1.5 px-3.5 rounded-full shadow-[0_2px_10px_rgba(0,0,0,0.03)]">
                            Pendaftaran akun pasien klinik baru.
                        </div>
                        <div class="bg-white/55 backdrop-blur-md border border-white/80 text-slate-600/90 text-[11px] font-medium py-1.5 px-3.5 rounded-full shadow-[0_2px_10px_rgba(0,0,0,0.03)]">
                            Daftar sekali untuk mengambil antrean kapan saja.
                        </div>
                        <div class="bg-white/55 backdrop-blur-md border border-white/80 text-slate-600/90 text-[11px] font-medium py-1.5 px-3.5 rounded-full shadow-[0_2px_10px_rgba(0,0,0,0.03)]">
                            Data identitas dan riwayat medis Anda terjamin aman.
                        </div>
                    </div>

                    <!-- Watermark Logo Besar Semi Transparan Tepat di Bawah Bubbles Ala Referensi -->
                    <div class="relative ml-8">
                        <span class="block text-[10px] tracking-[0.3em] font-semibold text-slate-400/80 uppercase mb-0.5">
                            SISTEM INFORMASI
                        </span>
                        <div class="text-6xl xl:text-7xl font-bold tracking-tight text-slate-800/85 flex items-center">
                            {{ clinicInfo.clinic_name.toLowerCase().replace(/\s+/g, '') }}
                        </div>
                        <span class="block text-[9px] tracking-[0.4em] font-medium text-slate-400/70 uppercase mt-1">
                            PELAYANAN KESEHATAN DIGITAL
                        </span>
                    </div>
                </div>

                <!-- Sisi Kanan (5 Kolom): Floating Glassmorphism Card Bersih Sesuai Gambar Referensi -->
                <div class="lg:col-span-5 w-full max-w-[390px] mx-auto lg:ml-auto">
                    <!-- Glassmorphism Card: Background Putih Semi-Transparan + Backdrop Blur + Sudut Membulat Halus -->
                    <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-[28px] p-7 sm:p-8 shadow-[0_20px_60px_-15px_rgba(59,130,246,0.12)] ring-1 ring-white/80">
                        
                        <!-- Judul Form (Teks Tebal Minimalis) -->
                        <h2 class="text-xl font-bold tracking-tight text-slate-900 mb-5">
                            Daftar Akun Baru
                        </h2>

                        <!-- Form Input Registrasi Sederhana -->
                        <Form
                            v-bind="store.form()"
                            :reset-on-success="['password', 'password_confirmation']"
                            v-slot="{ errors, processing }"
                            class="space-y-3"
                        >
                            <!-- Input 1: Nama Lengkap / Username -->
                            <div>
                                <Input
                                    id="username"
                                    type="text"
                                    name="username"
                                    required
                                    v-focus
                                    :tabindex="1"
                                    autocomplete="username"
                                    placeholder="Nama Lengkap / Username"
                                    class="h-9.5 rounded-xl bg-white/70 border-slate-200/70 focus:bg-white text-xs px-3.5 text-slate-800 placeholder:text-slate-400 transition shadow-none focus-visible:ring-1 focus-visible:ring-blue-500"
                                />
                                <InputError :message="errors.username || errors.name" class="mt-1" />
                            </div>

                            <!-- Input 2: Email -->
                            <div>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    :tabindex="2"
                                    autocomplete="email"
                                    placeholder="Alamat Email"
                                    class="h-9.5 rounded-xl bg-white/70 border-slate-200/70 focus:bg-white text-xs px-3.5 text-slate-800 placeholder:text-slate-400 transition shadow-none focus-visible:ring-1 focus-visible:ring-blue-500"
                                />
                                <InputError :message="errors.email" class="mt-1" />
                            </div>

                            <!-- Input 3: Kata Sandi -->
                            <div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    :tabindex="3"
                                    autocomplete="new-password"
                                    placeholder="Kata Sandi"
                                    class="h-9.5 rounded-xl bg-white/70 border-slate-200/70 focus:bg-white text-xs px-3.5 text-slate-800 placeholder:text-slate-400 transition shadow-none focus-visible:ring-1 focus-visible:ring-blue-500"
                                    :passwordrules="passwordRules"
                                />
                                <InputError :message="errors.password" class="mt-1" />
                            </div>

                            <!-- Input 4: Konfirmasi Kata Sandi -->
                            <div>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    required
                                    :tabindex="4"
                                    autocomplete="new-password"
                                    placeholder="Ulangi Kata Sandi"
                                    class="h-9.5 rounded-xl bg-white/70 border-slate-200/70 focus:bg-white text-xs px-3.5 text-slate-800 placeholder:text-slate-400 transition shadow-none focus-visible:ring-1 focus-visible:ring-blue-500"
                                    :passwordrules="passwordRules"
                                />
                                <InputError :message="errors.password_confirmation" class="mt-1" />
                            </div>

                            <!-- Checkbox Persetujuan Syarat & Ketentuan -->
                            <div class="flex items-center gap-2 pt-0.5">
                                <Checkbox
                                    id="terms-register"
                                    v-model="agreeTerms"
                                    class="size-3.5 rounded-sm border-slate-300 data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600"
                                />
                                <label for="terms-register" class="text-[11px] text-slate-500 select-none cursor-pointer leading-tight">
                                    Saya setuju dengan <span class="text-blue-600">Syarat Layanan</span> & <span class="text-blue-600">Privasi</span>
                                </label>
                            </div>

                            <!-- Tombol Masuk Biru Terang Solid Sesuai Referensi -->
                            <Button
                                type="submit"
                                :disabled="processing || !agreeTerms"
                                class="w-full h-10 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs shadow-md shadow-blue-500/25 transition-all cursor-pointer mt-1"
                                :tabindex="5"
                                data-test="register-user-button"
                            >
                                <Spinner v-if="processing" class="size-4" />
                                <span v-else>Daftar Sekarang</span>
                            </Button>

                            <!-- Link Bawah: Sudah punya akun? Masuk -->
                            <div class="text-center pt-1.5">
                                <p class="text-[11px] text-slate-400">
                                    Sudah punya akun?
                                    <Link
                                        :href="login()"
                                        class="text-blue-600 hover:underline font-medium ml-1"
                                    >
                                        Masuk
                                    </Link>
                                </p>
                            </div>
                        </Form>

                    </div>
                </div>

            </div>
        </main>

        <!-- 3. Footer Sederhana (Hanya Ruang Bawah Kosong Bersih) -->
        <div class="relative z-10 shrink-0 h-4"></div>
    </div>
</template>
