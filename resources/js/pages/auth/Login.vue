<script setup lang="ts">
import { computed, ref } from 'vue';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const page = usePage();
const clinicInfo = computed(() => {
    return (
        (page.props as any).clinic || {
            clinic_name: 'KLINIK SEHAT',
        }
    );
});

const agreeTerms = ref(true);
</script>

<template>
    <Head title="Masuk Akun" />

    <!-- Container Full Viewport Lock (h-screen w-screen overflow-hidden) Pas Tanpa Scroll -->
    <div
        class="relative box-border flex h-screen w-screen flex-col justify-between overflow-hidden bg-[#f2f6fc] p-6 font-sans text-slate-900 select-none sm:p-10 md:p-12"
    >
        <!-- Background Ambient Smooth Silky Wave & Mesh Gradasi Sesuai Referensi -->
        <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">
            <!-- Sky Blue & Lavender Soft Background Gradients -->
            <div
                class="pointer-events-none absolute -top-1/4 left-0 h-full w-full bg-radial from-[#e0efff]/80 via-[#edf4fd]/60 to-transparent blur-3xl"
            />

            <!-- Gelombang Halus Biru Keunguan di Tengah Menyeberang ke Bawah (Wave Flow) -->
            <div
                class="absolute top-[35%] -left-[10%] h-[55%] w-[120%] -rotate-[7deg] transform rounded-[100%] bg-gradient-to-r from-blue-300/35 via-indigo-400/30 to-purple-400/25 blur-[90px]"
            />
            <div
                class="absolute top-[48%] -left-[5%] h-[40%] w-[110%] -rotate-[4deg] transform rounded-[100%] bg-gradient-to-r from-blue-400/25 via-indigo-500/20 to-blue-300/15 blur-[70px]"
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

        <!-- 1. Header Pojok Kiri Atas: Logo Sederhana & Modern Sesuai Referensi -->
        <header
            class="relative z-10 flex shrink-0 items-center justify-between"
        >
            <Link href="/" class="group flex items-center gap-2.5">
                <!-- Simbol Ikon Neovos Style -->
                <div class="relative flex size-8 items-center justify-center">
                    <span
                        class="absolute top-0 -left-1 text-lg font-bold text-indigo-500 select-none"
                        >✦</span
                    >
                    <div
                        class="flex size-6 items-center justify-center rounded-full bg-gradient-to-tr from-indigo-600 to-purple-500 shadow-xs"
                    >
                        <div class="size-2 rounded-full bg-white"></div>
                    </div>
                </div>
                <span
                    class="text-xl font-bold tracking-tight text-slate-800 lowercase"
                >
                    {{
                        clinicInfo.clinic_name.toLowerCase().replace(/\s+/g, '')
                    }}
                </span>
            </Link>
        </header>

        <!-- 2. Main Center Body: Pas Layar (Sisi Kiri Typo + Sisi Kanan Floating Glass Card) -->
        <main
            class="relative z-10 mx-auto my-auto flex w-full max-w-6xl flex-1 items-center justify-between px-4 lg:px-8"
        >
            <div
                class="grid w-full grid-cols-1 items-center gap-8 lg:grid-cols-12"
            >
                <!-- Sisi Kiri (7 Kolom): Elegan, Bersih, Floating Chat Pill Halus Transparan Ala Gambar Referensi -->
                <div
                    class="relative hidden flex-col items-start justify-center pl-6 select-none lg:col-span-7 lg:flex"
                >
                    <!-- 4 Chat Bubbles Transparan Bertumpuk Halus di Atas Teks Logo -->
                    <div class="mb-5 ml-14 flex flex-col items-start gap-1.5">
                        <div
                            class="h-3 w-16 rounded-full border border-white/60 bg-white/40 shadow-[0_2px_8px_rgba(0,0,0,0.03)] backdrop-blur-md"
                        ></div>
                        <div
                            class="rounded-full border border-white/80 bg-white/55 px-3.5 py-1.5 text-[11px] font-medium text-slate-600/90 shadow-[0_2px_10px_rgba(0,0,0,0.03)] backdrop-blur-md"
                        >
                            Selamat datang di sistem antrean klinik.
                        </div>
                        <div
                            class="rounded-full border border-white/80 bg-white/55 px-3.5 py-1.5 text-[11px] font-medium text-slate-600/90 shadow-[0_2px_10px_rgba(0,0,0,0.03)] backdrop-blur-md"
                        >
                            Silakan masukkan akun Anda untuk memulai.
                        </div>
                        <div
                            class="rounded-full border border-white/80 bg-white/55 px-3.5 py-1.5 text-[11px] font-medium text-slate-600/90 shadow-[0_2px_10px_rgba(0,0,0,0.03)] backdrop-blur-md"
                        >
                            Akses rekam medis, antrean, dan resep secara instan.
                        </div>
                    </div>

                    <!-- Watermark Logo Besar Semi Transparan Tepat di Bawah Bubbles Ala Referensi -->
                    <div class="relative ml-8">
                        <span
                            class="mb-0.5 block text-[10px] font-semibold tracking-[0.3em] text-slate-400/80 uppercase"
                        >
                            SISTEM INFORMASI
                        </span>
                        <div
                            class="flex items-center text-6xl font-bold tracking-tight text-slate-800/85 xl:text-7xl"
                        >
                            {{
                                clinicInfo.clinic_name
                                    .toLowerCase()
                                    .replace(/\s+/g, '')
                            }}
                        </div>
                        <span
                            class="mt-1 block text-[9px] font-medium tracking-[0.4em] text-slate-400/70 uppercase"
                        >
                            PELAYANAN KESEHATAN DIGITAL
                        </span>
                    </div>
                </div>

                <!-- Sisi Kanan (5 Kolom): Floating Glassmorphism Card Bersih Sesuai Gambar Referensi -->
                <div
                    class="mx-auto w-full max-w-[390px] lg:col-span-5 lg:ml-auto"
                >
                    <!-- Glassmorphism Card: Background Putih Semi-Transparan + Backdrop Blur + Sudut Membulat Halus -->
                    <div
                        class="rounded-[28px] border border-white/90 bg-white/65 p-7 shadow-[0_20px_60px_-15px_rgba(59,130,246,0.12)] ring-1 ring-white/80 backdrop-blur-2xl sm:p-8"
                    >
                        <!-- Judul Form (Teks Tebal Minimalis) -->
                        <h2
                            class="mb-5 text-xl font-bold tracking-tight text-slate-900"
                        >
                            Masuk ke Akun
                        </h2>

                        <!-- Status Alert Notification -->
                        <div
                            v-if="status"
                            class="mb-3 rounded-xl border border-emerald-100 bg-emerald-50 p-2.5 text-center text-xs font-medium text-emerald-700"
                        >
                            {{ status }}
                        </div>

                        <!-- Form Input Sederhana -->
                        <Form
                            v-bind="store.form()"
                            :reset-on-success="['password']"
                            v-slot="{ errors, processing }"
                            class="space-y-3.5"
                        >
                            <!-- Input 1: Field Email -->
                            <div>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    v-focus
                                    :tabindex="1"
                                    autocomplete="email"
                                    placeholder="Masukkan Alamat Email"
                                    class="h-10 rounded-xl border-slate-200/70 bg-white/70 px-3.5 text-xs text-slate-800 shadow-none transition placeholder:text-slate-400 focus:bg-white focus-visible:ring-1 focus-visible:ring-blue-500"
                                />
                                <InputError
                                    :message="errors.email"
                                    class="mt-1"
                                />
                            </div>

                            <!-- Input 2: Kata Sandi (Minimalis dengan Tombol Mata) -->
                            <div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="Kata Sandi"
                                    class="h-10 rounded-xl border-slate-200/70 bg-white/70 px-3.5 text-xs text-slate-800 shadow-none transition placeholder:text-slate-400 focus:bg-white focus-visible:ring-1 focus-visible:ring-blue-500"
                                />
                                <InputError
                                    :message="errors.password"
                                    class="mt-1"
                                />
                            </div>

                            <!-- Checkbox Persetujuan Syarat & Ketentuan -->
                            <div class="flex items-center gap-2 pt-1">
                                <Checkbox
                                    id="terms"
                                    v-model="agreeTerms"
                                    class="size-3.5 rounded-sm border-slate-300 data-[state=checked]:border-indigo-600 data-[state=checked]:bg-indigo-600"
                                />
                                <label
                                    for="terms"
                                    class="cursor-pointer text-[11px] leading-tight text-slate-500 select-none"
                                >
                                    Saya telah membaca dan menyetujui
                                    <span class="text-indigo-600"
                                        >Syarat Layanan</span
                                    >
                                    &
                                    <span class="text-indigo-600">Privasi</span>
                                </label>
                            </div>

                            <!-- Tombol Masuk Ungu / Indigo Khas Project -->
                            <Button
                                type="submit"
                                :disabled="processing || !agreeTerms"
                                class="mt-1 h-10 w-full cursor-pointer rounded-xl bg-indigo-600 text-xs font-medium text-white shadow-md shadow-indigo-600/25 transition-all hover:bg-indigo-700"
                                :tabindex="3"
                                data-test="login-button"
                            >
                                <Spinner v-if="processing" class="size-4" />
                                <span v-else>Masuk</span>
                            </Button>

                            <!-- Link Bawah: Belum Ada Akun? Daftar Sesuai Referensi -->
                            <div class="pt-1.5 text-center">
                                <p class="text-[11px] text-slate-400">
                                    Belum punya akun?
                                    <Link
                                        :href="register()"
                                        class="ml-1 font-medium text-indigo-600 hover:underline"
                                    >
                                        Daftar
                                    </Link>
                                </p>
                            </div>
                        </Form>
                    </div>
                </div>
            </div>
        </main>

        <!-- 3. Footer Sederhana (Hanya Ruang Bawah Kosong Bersih Tanpa Elemen Mengganggu) -->
        <div class="relative z-10 h-4 shrink-0"></div>
    </div>
</template>
