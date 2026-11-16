<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    settings: {
        clinic_name: string;
        clinic_address: string;
        clinic_phone: string;
    };
}>();

const form = useForm({
    clinic_name: props.settings?.clinic_name || '',
    clinic_address: props.settings?.clinic_address || '',
    clinic_phone: props.settings?.clinic_phone || '',
});

const submit = () => {
    form.patch('/settings/clinic', {
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
            description="Atur nama klinik, alamat, dan nomor kontak yang akan ditampilkan pada Nota/Invoice dan Layar Antrean."
        />

        <form @submit.prevent="submit" class="space-y-5">
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
