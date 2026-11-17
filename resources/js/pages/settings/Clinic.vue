<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Image, Upload } from 'lucide-vue-next';

const props = defineProps<{
    settings: {
        clinic_name: string;
        clinic_address: string;
        clinic_phone: string;
        clinic_logo?: string | null;
        clinic_logo_url?: string | null;
        clinic_banner?: string | null;
        clinic_banner_url?: string | null;
        default_examination_fee?: number;
        queue_prefix?: string;
    };
}>();

const previewUrl = ref<string | null>(props.settings?.clinic_logo_url || null);
const bannerPreviewUrl = ref<string | null>(props.settings?.clinic_banner_url || '/images/backround_display.jpg');

const form = useForm({
    clinic_name: props.settings?.clinic_name || '',
    clinic_address: props.settings?.clinic_address || '',
    clinic_phone: props.settings?.clinic_phone || '',
    default_examination_fee: props.settings?.default_examination_fee ?? 50000,
    queue_prefix: props.settings?.queue_prefix || 'A',
    clinic_logo: null as File | null,
    clinic_banner: null as File | null,
});

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.clinic_logo = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

const onBannerChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.clinic_banner = file;
        bannerPreviewUrl.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    // Gunakan POST dengan header / _method spoofing untuk file upload di Inertia
    form.post('/settings/clinic', {
        headers: {
            'X-HTTP-Method-Override': 'PATCH',
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pengaturan Klinik" />

    <h1 class="sr-only">Pengaturan Klinik</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Informasi & Profil Klinik"
            description="Atur identitas klinik, logo resmi, alamat, dan kontak untuk kop surat invoice serta layar display antrean."
        />

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Upload Logo Klinik -->
            <div class="space-y-2">
                <Label>Logo Klinik</Label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                        <img
                            v-if="previewUrl"
                            :src="previewUrl"
                            alt="Logo Klinik"
                            class="w-full h-full object-contain p-1"
                        />
                        <Image v-else class="w-7 h-7 text-slate-400" />
                    </div>

                    <div class="space-y-1">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 cursor-pointer shadow-2xs text-slate-700 dark:text-slate-200 transition">
                            <Upload class="w-3.5 h-3.5" />
                            Pilih Logo Baru
                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                @change="onFileChange"
                                class="hidden"
                            />
                        </label>
                        <p class="text-[11px] text-muted-foreground">
                            Format JPG, PNG, atau WebP (Maks. 2MB). Ditampilkan di header sidebar, invoice, & display TV.
                        </p>
                    </div>
                </div>
                <InputError :message="form.errors.clinic_logo" />
            </div>

            <!-- Banner / Hero Display Background -->
            <div class="space-y-2">
                <Label>Gambar Banner Klinik & Background Display TV</Label>
                <div class="space-y-3">
                    <div class="w-full max-w-lg h-44 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 overflow-hidden relative shadow-xs">
                        <img
                            v-if="bannerPreviewUrl"
                            :src="bannerPreviewUrl"
                            alt="Banner Preview"
                            class="size-full object-cover"
                        />
                        <div v-else class="size-full flex flex-col items-center justify-center text-slate-400 gap-1">
                            <Image class="w-8 h-8 opacity-40" />
                            <span class="text-xs">Belum ada banner diunggah</span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 cursor-pointer shadow-2xs text-slate-700 dark:text-slate-200 transition">
                            <Upload class="w-3.5 h-3.5" />
                            Pilih Banner Baru
                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                @change="onBannerChange"
                                class="hidden"
                            />
                        </label>
                        <p class="text-[11px] text-muted-foreground">
                            Format JPG, PNG, atau WebP (Maks. 5MB). Digunakan untuk banner foto utama Landing Page dan background layar monitor antrean (Display Queue).
                        </p>
                    </div>
                </div>
                <InputError :message="form.errors.clinic_banner" />
            </div>

            <div class="space-y-2">
                <Label for="clinic_name">Nama Klinik</Label>
                <Input
                    id="clinic_name"
                    v-model="form.clinic_name"
                    type="text"
                    required
                    placeholder="Contoh: KLINIK PRATAMA SEHAT"
                    :class="{ 'border-destructive': form.errors.clinic_name }"
                />
                <p class="text-xs text-muted-foreground">
                    Nama ini akan muncul pada kuitansi/invoice serta judul papan antrean layar TV (Display Queue).
                </p>
                <InputError :message="form.errors.clinic_name" />
            </div>

            <div class="space-y-2">
                <Label for="clinic_address">Alamat Lengkap</Label>
                <Input
                    id="clinic_address"
                    v-model="form.clinic_address"
                    type="text"
                    required
                    placeholder="Contoh: Jl. Kesehatan No. 123, Kota Sejahtera"
                    :class="{ 'border-destructive': form.errors.clinic_address }"
                />
                <p class="text-xs text-muted-foreground">
                    Alamat klinik akan tertera pada kop surat faktur / cetak invoice.
                </p>
                <InputError :message="form.errors.clinic_address" />
            </div>

            <div class="space-y-2">
                <Label for="clinic_phone">No. Telepon / Kontak</Label>
                <Input
                    id="clinic_phone"
                    v-model="form.clinic_phone"
                    type="text"
                    placeholder="Contoh: (021) 555-0123"
                    :class="{ 'border-destructive': form.errors.clinic_phone }"
                />
                <p class="text-xs text-muted-foreground">
                    Nomor telepon atau kontak resmi klinik pada kuitansi/invoice.
                </p>
                <InputError :message="form.errors.clinic_phone" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label for="default_examination_fee">Tarif Pemeriksaan Dokter Bawaan (Rp)</Label>
                    <Input
                        id="default_examination_fee"
                        v-model="form.default_examination_fee"
                        type="number"
                        min="0"
                        placeholder="50000"
                        :class="{ 'border-destructive': form.errors.default_examination_fee }"
                    />
                    <p class="text-xs text-muted-foreground">
                        Biaya jasa standar rekam medis & invoice baru.
                    </p>
                    <InputError :message="form.errors.default_examination_fee" />
                </div>

                <div class="space-y-2">
                    <Label for="queue_prefix">Format Prefix Nomor Antrean Default</Label>
                    <Input
                        id="queue_prefix"
                        v-model="form.queue_prefix"
                        type="text"
                        maxlength="10"
                        placeholder="Contoh: A, ANT, KLN"
                        class="uppercase font-mono"
                        :class="{ 'border-destructive': form.errors.queue_prefix }"
                    />
                    <p class="text-xs text-muted-foreground">
                        Awalan nomor tiket antrean (misal: <strong>A</strong>-001). Bisa di-override per dokter di Data Dokter.
                    </p>
                    <InputError :message="form.errors.queue_prefix" />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer"
                >
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </Button>

                <p
                    v-if="form.recentlySuccessful"
                    class="text-xs text-emerald-600 font-medium"
                >
                    Tersimpan.
                </p>
            </div>
        </form>
    </div>
</template>
