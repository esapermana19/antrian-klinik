<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Printer, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    invoice: Object,
});

const page = usePage();
const clinicInfo = computed(() => {
    return (
        page.props.clinic || {
            clinic_name: 'KLINIK PRATAMA SEHAT',
            clinic_address: 'Jl. Kesehatan No. 123, Kota Sejahtera',
            clinic_phone: '(021) 555-0123',
        }
    );
});

const printInvoice = () => {
    window.print();
};

const formatRupiah = (val) => {
    if (!val && val !== 0) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <Head :title="`Nota Pembayaran - ${invoice.invoice_code}`" />

    <div
        class="mx-auto max-w-4xl space-y-4 p-4 md:p-6 print:m-0 print:max-w-none print:p-0"
    >
        <!-- Top Action Bar (Disembunyikan saat cetak) -->
        <div
            class="flex items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-xs print:hidden"
        >
            <Link href="/invoices">
                <Button
                    variant="outline"
                    size="sm"
                    class="h-8 cursor-pointer gap-1.5 border-slate-200 text-xs text-slate-700 hover:bg-slate-50"
                >
                    <ArrowLeft class="size-3.5" />
                    Kembali ke Kasir
                </Button>
            </Link>

            <Button
                size="sm"
                class="h-8 cursor-pointer gap-1.5 bg-slate-900 text-xs font-medium text-white shadow-xs hover:bg-black"
                @click="printInvoice"
            >
                <Printer class="size-3.5" />
                Cetak Invoice
            </Button>
        </div>

        <!-- TAMPILAN INVOICE HITAM PUTIH RAPI (A4 / STANDAR) -->
        <div
            class="standard-invoice rounded-none border border-black bg-white p-6 text-black shadow-xs md:p-8 print:m-0 print:border-none print:p-0 print:shadow-none"
        >
            <!-- Header Nota Klinik -->
            <div class="border-b-2 border-black pb-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="clinicInfo.clinic_logo_url"
                            :src="clinicInfo.clinic_logo_url"
                            alt="Logo Klinik"
                            class="h-12 w-auto max-w-[120px] shrink-0 object-contain"
                        />
                        <div>
                            <h1
                                class="text-base font-black tracking-wider text-black uppercase"
                            >
                                {{ clinicInfo.clinic_name }}
                            </h1>
                            <p class="mt-0.5 text-xs text-black">
                                {{ clinicInfo.clinic_address
                                }}<span v-if="clinicInfo.clinic_phone">
                                    • Telp: {{ clinicInfo.clinic_phone }}</span
                                >
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <p
                            class="text-xs font-bold tracking-widest text-black uppercase"
                        >
                            {{
                                invoice.payment_status === 'paid'
                                    ? 'BUKTI PEMBAYARAN (LUNAS)'
                                    : 'TAGIHAN PEMERIKSAAN'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Metadata Pasien & Tagihan -->
            <div
                class="grid grid-cols-2 gap-4 border-b border-black py-3 text-xs"
            >
                <div class="space-y-1">
                    <p
                        class="text-[10px] font-bold tracking-wider text-black uppercase"
                    >
                        PASIEN
                    </p>
                    <p class="text-sm font-bold text-black">
                        {{ invoice.medical_record?.patient?.patient_name }}
                    </p>
                    <p class="font-mono text-black">
                        NIK: {{ invoice.medical_record?.patient?.nik || '-' }}
                    </p>
                    <p class="text-black">
                        No. Antrean:
                        <span class="font-bold">{{
                            invoice.medical_record?.queue?.queue_number || '-'
                        }}</span>
                    </p>
                </div>
                <div class="space-y-1 text-right">
                    <p
                        class="text-[10px] font-bold tracking-wider text-black uppercase"
                    >
                        INFORMASI NOTA
                    </p>
                    <p class="font-mono text-sm font-bold text-black">
                        {{ invoice.invoice_code }}
                    </p>
                    <p class="text-black">
                        {{ formatDate(invoice.created_at) }}
                    </p>
                    <p class="text-black">
                        Status:
                        <span class="font-bold uppercase">{{
                            invoice.payment_status === 'paid'
                                ? 'LUNAS'
                                : 'BELUM LUNAS'
                        }}</span>
                        <span v-if="invoice.payment_status === 'paid'">
                            ({{
                                invoice.payment_method === 'cash'
                                    ? 'Tunai'
                                    : 'Transfer'
                            }})</span
                        >
                    </p>
                </div>
            </div>

            <!-- Tabel Rincian Hitam Putih Bersih -->
            <div class="space-y-3 py-4">
                <table
                    class="w-full border-collapse border border-black text-xs"
                >
                    <thead>
                        <tr class="border-b border-black">
                            <th
                                class="border-r border-black p-2 text-left text-[11px] font-bold text-black uppercase"
                            >
                                Uraian Layanan / Resep
                            </th>
                            <th
                                class="w-16 border-r border-black p-2 text-center text-[11px] font-bold text-black uppercase"
                            >
                                Qty
                            </th>
                            <th
                                class="w-28 border-r border-black p-2 text-right text-[11px] font-bold text-black uppercase"
                            >
                                Harga Satuan
                            </th>
                            <th
                                class="w-32 p-2 text-right text-[11px] font-bold text-black uppercase"
                            >
                                Subtotal
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Pemeriksaan Dokter -->
                        <tr class="border-b border-black">
                            <td class="border-r border-black p-2 text-black">
                                <span class="font-semibold"
                                    >Jasa Pemeriksaan & Konsultasi Dokter</span
                                >
                                <div class="text-[11px] text-black">
                                    Diagnosis:
                                    {{
                                        invoice.medical_record?.diagnosis || '-'
                                    }}
                                </div>
                            </td>
                            <td
                                class="border-r border-black p-2 text-center font-mono text-black"
                            >
                                1
                            </td>
                            <td
                                class="border-r border-black p-2 text-right font-mono text-black"
                            >
                                {{ formatRupiah(invoice.examination_fee) }}
                            </td>
                            <td
                                class="p-2 text-right font-mono font-bold text-black"
                            >
                                {{ formatRupiah(invoice.examination_fee) }}
                            </td>
                        </tr>

                        <!-- Daftar Obat -->
                        <tr
                            v-for="item in invoice.medical_record
                                ?.medication_prescriptions"
                            :key="item.prescription_id"
                            class="border-b border-black"
                        >
                            <td class="border-r border-black p-2 text-black">
                                <span class="font-semibold">{{
                                    item.medication?.medication_name || 'Obat'
                                }}</span>
                                <div class="text-[11px] text-black">
                                    Dosis: {{ item.dosage || '-' }}
                                </div>
                            </td>
                            <td
                                class="border-r border-black p-2 text-center font-mono text-black"
                            >
                                {{ item.qty }}
                            </td>
                            <td
                                class="border-r border-black p-2 text-right font-mono text-black"
                            >
                                {{ formatRupiah(item.unit_price) }}
                            </td>
                            <td
                                class="p-2 text-right font-mono font-bold text-black"
                            >
                                {{ formatRupiah(item.total_price) }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Total -->
                <div class="flex justify-end pt-2">
                    <div class="w-72 space-y-1.5 text-xs">
                        <div class="flex justify-between text-black">
                            <span>Subtotal Pemeriksaan:</span>
                            <span class="font-mono">{{
                                formatRupiah(invoice.examination_fee)
                            }}</span>
                        </div>
                        <div class="flex justify-between text-black">
                            <span>Subtotal Resep Obat:</span>
                            <span class="font-mono">{{
                                formatRupiah(invoice.medication_fee)
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between border-t-2 border-black pt-1.5 text-xs"
                        >
                            <span class="font-bold text-black uppercase"
                                >Total Tagihan:</span
                            >
                            <span
                                class="font-mono text-base font-black text-black"
                                >{{ formatRupiah(invoice.total_amount) }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tanda Tangan Kasir -->
            <div
                class="mt-4 grid grid-cols-2 border-t border-black pt-6 text-center text-xs text-black"
            >
                <div>
                    <p class="font-semibold">Pasien</p>
                    <div class="h-14"></div>
                    <p class="font-bold">
                        ({{ invoice.medical_record?.patient?.patient_name }})
                    </p>
                </div>
                <div>
                    <p class="font-semibold">Petugas Kasir</p>
                    <div class="h-14"></div>
                    <p class="font-bold">
                        ( ........................................ )
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    /* Sembunyikan semua elemen layout pembungkus browser / sidebar */
    aside,
    header,
    nav,
    .print\:hidden {
        display: none !important;
    }

    body {
        background-color: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hilangkan margin / padding wrapper pada layout utama */
    main {
        padding: 0 !important;
        margin: 0 !important;
    }

    @page {
        margin: 8mm;
        size: auto;
    }
}
</style>
