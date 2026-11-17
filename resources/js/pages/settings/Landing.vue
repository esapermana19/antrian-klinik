<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Sparkles, Save, Layers, UserCheck, CalendarDays, PhoneCall } from 'lucide-vue-next';

const props = defineProps<{
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
}>();

const form = useForm({
    hero_badge: props.landing?.hero_badge || '',
    hero_title: props.landing?.hero_title || '',
    hero_subtitle: props.landing?.hero_subtitle || '',
    cta_primary_text: props.landing?.cta_primary_text || '',
    cta_secondary_text: props.landing?.cta_secondary_text || '',
    features_section_title: props.landing?.features_section_title || '',
    features_section_subtitle: props.landing?.features_section_subtitle || '',
    doctors_section_title: props.landing?.doctors_section_title || '',
    doctors_section_subtitle: props.landing?.doctors_section_subtitle || '',
    schedules_section_title: props.landing?.schedules_section_title || '',
    schedules_section_subtitle: props.landing?.schedules_section_subtitle || '',
    emergency_contact: props.landing?.emergency_contact || '',
});

const submit = () => {
    form.patch('/settings/landing', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-6">
        <Head title="Pengaturan Landing Page" />

        <Heading
            title="Konten Landing Page (CMS)"
            description="Sesuaikan teks headline, subheadline, tombol CTA, dan judul section pada halaman utama klinik."
        />

        <form @submit.prevent="submit" class="space-y-8">
            <!-- Hero Section Group -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="p-1.5 rounded-lg bg-sky-50 text-sky-600">
                        <Sparkles class="size-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Bagian Utama (Hero Section)</h3>
                        <p class="text-xs text-slate-500">Banner atmosferik atas dengan sky-gradient lembut.</p>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div class="grid gap-1.5">
                        <Label for="hero_badge">Badge / Teks Pill Kecil</Label>
                        <Input
                            id="hero_badge"
                            v-model="form.hero_badge"
                            placeholder="Contoh: Pelayanan Kesehatan Modern & Ramah"
                            class="text-xs"
                        />
                        <InputError :message="form.errors.hero_badge" />
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="hero_title">Headline Utama (Judul Besar) <span class="text-rose-500">*</span></Label>
                        <Input
                            id="hero_title"
                            v-model="form.hero_title"
                            placeholder="Contoh: Perawatan Medis Terpercaya untuk Keluarga Anda"
                            class="text-xs"
                            required
                        />
                        <InputError :message="form.errors.hero_title" />
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="hero_subtitle">Subheadline (Deskripsi Pendukung) <span class="text-rose-500">*</span></Label>
                        <textarea
                            id="hero_subtitle"
                            v-model="form.hero_subtitle"
                            rows="3"
                            placeholder="Tuliskan pengantar singkat tentang keunggulan klinik..."
                            class="w-full rounded-md border border-slate-200 px-3 py-2 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                            required
                        ></textarea>
                        <InputError :message="form.errors.hero_subtitle" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="grid gap-1.5">
                            <Label for="cta_primary">Teks Tombol Utama (CTA 1)</Label>
                            <Input
                                id="cta_primary"
                                v-model="form.cta_primary_text"
                                placeholder="Contoh: Daftar Antrean Sekarang"
                                class="text-xs"
                            />
                            <InputError :message="form.errors.cta_primary_text" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="cta_secondary">Teks Tombol Kedua (CTA 2)</Label>
                            <Input
                                id="cta_secondary"
                                v-model="form.cta_secondary_text"
                                placeholder="Contoh: Lihat Jadwal Dokter"
                                class="text-xs"
                            />
                            <InputError :message="form.errors.cta_secondary_text" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section Group -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">
                        <Layers class="size-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Bagian Fitur & Keunggulan</h3>
                        <p class="text-xs text-slate-500">Judul blok fitur pelayanan klinik.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-1.5">
                        <Label for="features_title">Judul Section Keunggulan</Label>
                        <Input
                            id="features_title"
                            v-model="form.features_section_title"
                            placeholder="Contoh: Mengapa Memilih Klinik Kami?"
                            class="text-xs"
                        />
                        <InputError :message="form.errors.features_section_title" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="features_subtitle">Deskripsi Singkat</Label>
                        <Input
                            id="features_subtitle"
                            v-model="form.features_section_subtitle"
                            placeholder="Contoh: Standar pelayanan medis terbaik dengan kemudahan..."
                            class="text-xs"
                        />
                        <InputError :message="form.errors.features_section_subtitle" />
                    </div>
                </div>
            </div>

            <!-- Doctors Section Group -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="p-1.5 rounded-lg bg-teal-50 text-teal-600">
                        <UserCheck class="size-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Bagian Profil Tim Dokter</h3>
                        <p class="text-xs text-slate-500">Daftar dokter dan spesialisasi yang tampil dinamis.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-1.5">
                        <Label for="doctors_title">Judul Section Dokter</Label>
                        <Input
                            id="doctors_title"
                            v-model="form.doctors_section_title"
                            placeholder="Contoh: Tim Dokter & Tenaga Medis"
                            class="text-xs"
                        />
                        <InputError :message="form.errors.doctors_section_title" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="doctors_subtitle">Deskripsi Singkat</Label>
                        <Input
                            id="doctors_subtitle"
                            v-model="form.doctors_section_subtitle"
                            placeholder="Contoh: Dokter berdedikasi tinggi dengan keahlian profesional..."
                            class="text-xs"
                        />
                        <InputError :message="form.errors.doctors_section_subtitle" />
                    </div>
                </div>
            </div>

            <!-- Schedules Section Group -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
                        <CalendarDays class="size-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Bagian Jadwal Praktik Mingguan</h3>
                        <p class="text-xs text-slate-500">Tabel ringkasan hari dan jam layanan klinik.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-1.5">
                        <Label for="schedules_title">Judul Section Jadwal</Label>
                        <Input
                            id="schedules_title"
                            v-model="form.schedules_section_title"
                            placeholder="Contoh: Jadwal Praktik Mingguan"
                            class="text-xs"
                        />
                        <InputError :message="form.errors.schedules_section_title" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="schedules_subtitle">Deskripsi Singkat</Label>
                        <Input
                            id="schedules_subtitle"
                            v-model="form.schedules_section_subtitle"
                            placeholder="Contoh: Pilih waktu konsultasi yang sesuai..."
                            class="text-xs"
                        />
                        <InputError :message="form.errors.schedules_section_subtitle" />
                    </div>
                </div>
            </div>

            <!-- Emergency / Footer Group -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="p-1.5 rounded-lg bg-rose-50 text-rose-600">
                        <PhoneCall class="size-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Kontak Darurat / Callout</h3>
                        <p class="text-xs text-slate-500">Teks kontak hotline yang disorot pada landing page.</p>
                    </div>
                </div>

                <div class="grid gap-1.5">
                    <Label for="emergency_contact">Teks Hotline / Kontak Darurat</Label>
                    <Input
                        id="emergency_contact"
                        v-model="form.emergency_contact"
                        placeholder="Contoh: Hubungi Hotline Darurat: (021) 555-0123"
                        class="text-xs"
                    />
                    <InputError :message="form.errors.emergency_contact" />
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <Button
                    type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs px-5 h-9 rounded-lg gap-2 shadow-xs"
                    :disabled="form.processing"
                >
                    <Save class="size-3.5" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan Landing' }}
                </Button>
            </div>
        </form>
    </div>
</template>
