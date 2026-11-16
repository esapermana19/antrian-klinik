<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Printer,
    ArrowLeft,
} from 'lucide-vue-next';

const props = defineProps({
    invoice: Object,
});

const page = usePage();
const clinicInfo = computed(() => {
    return page.props.clinic || {
        clinic_name: 'KLINIK PRATAMA SEHAT',
        clinic_address: 'Jl. Kesehatan No. 123, Kota Sejahtera',
        clinic_phone: '(021) 555-0123',
    };
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

    <div class="space-y-4 p-4 md:p-6 max-w-4xl mx-auto print:max-w-none print:p-0 print:m-0">
        <!-- Top Action Bar (Disembunyikan saat cetak) -->
        <div class="flex items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-xs print:hidden">
            <Link href="/invoices">
                <Button variant="outline" size="sm" class="h-8 text-xs gap-1.5 text-slate-700 border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <ArrowLeft class="size-3.5" />
                    Kembali ke Kasir
                </Button>
            </Link>

            <Button
                size="sm"
                class="h-8 text-xs bg-slate-900 hover:bg-black text-white font-medium gap-1.5 shadow-xs cursor-pointer"
                @click="printInvoice"
            >
                <Printer class="size-3.5" />
                Cetak Invoice
            </Button>
        </div>

        <!-- TAMPILAN INVOICE HITAM PUTIH RAPI (A4 / STANDAR) -->
        <div
            class="standard-invoice bg-white border border-black p-6 md:p-8 text-black shadow-xs rounded-none print:border-none print:shadow-none print:p-0 print:m-0"
        >
            <!-- Header Nota Klinik -->
            <div class="border-b-2 border-black pb-3 text-center">
                <h1 class="text-lg font-black tracking-wider uppercase text-black">
                    {{ clinicInfo.clinic_name }}
                </h1>
                <p class="text-xs text-black mt-0.5">
                    {{ clinicInfo.clinic_address }}<span v-if="clinicInfo.clinic_phone"> • Telp: {{ clinicInfo.clinic_phone }}</span>
                </p>
                <p class="text-xs font-bold uppercase tracking-widest text-black mt-2">
                    {{ invoice.payment_status === 'paid' ? 'BUKTI PEMBAYARAN (LUNAS)' : 'TAGIHAN BIAYA PEMERIKSAAN' }}
                </p>
            </div>

            <!-- Metadata Pasien & Tagihan -->
            <div class="grid grid-cols-2 gap-4 py-3 text-xs border-b border-black">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-black uppercase tracking-wider">PASIEN</p>
                    <p class="font-bold text-black text-sm">{{ invoice.medical_record?.patient?.patient_name }}</p>
                    <p class="text-black font-mono">NIK: {{ invoice.medical_record?.patient?.nik || '-' }}</p>
                    <p class="text-black">No. Antrean: <span class="font-bold">{{ invoice.medical_record?.queue?.queue_number || '-' }}</span></p>
                </div>
                <div class="space-y-1 text-right">
                    <p class="text-[10px] font-bold text-black uppercase tracking-wider">INFORMASI NOTA</p>
                    <p class="font-bold font-mono text-black text-sm">{{ invoice.invoice_code }}</p>
                    <p class="text-black">{{ formatDate(invoice.created_at) }}</p>
                    <p class="text-black">
                        Status: <span class="font-bold uppercase">{{ invoice.payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}</span>
                        <span v-if="invoice.payment_status === 'paid'"> ({{ invoice.payment_method === 'cash' ? 'Tunai' : 'Transfer' }})</span>
                    </p>
                </div>
            </div>

            <!-- Tabel Rincian Hitam Putih Bersih -->
            <div class="py-4 space-y-3">
                <table class="w-full text-xs border-collapse border border-black">
                    <thead>
                        <tr class="border-b border-black">
                            <th class="border-r border-black p-2 text-left font-bold text-black uppercase text-[11px]">Uraian Layanan / Resep</th>
                            <th class="border-r border-black p-2 text-center w-16 font-bold text-black uppercase text-[11px]">Qty</th>
                            <th class="border-r border-black p-2 text-right w-28 font-bold text-black uppercase text-[11px]">Harga Satuan</th>
                            <th class="p-2 text-right w-32 font-bold text-black uppercase text-[11px]">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Pemeriksaan Dokter -->
                        <tr class="border-b border-black">
                            <td class="border-r border-black p-2 text-black">
                                <span class="font-semibold">Jasa Pemeriksaan & Konsultasi Dokter</span>
                                <div class="text-[11px] text-black">Diagnosis: {{ invoice.medical_record?.diagnosis || '-' }}</div>
                            </td>
                            <td class="border-r border-black p-2 text-center text-black font-mono">1</td>
                            <td class="border-r border-black p-2 text-right font-mono text-black">{{ formatRupiah(invoice.examination_fee) }}</td>
                            <td class="p-2 text-right font-mono font-bold text-black">{{ formatRupiah(invoice.examination_fee) }}</td>
                        </tr>

                        <!-- Daftar Obat -->
                        <tr
                            v-for="item in invoice.medical_record?.medication_prescriptions"
                            :key="item.prescription_id"
                            class="border-b border-black"
                        >
                            <td class="border-r border-black p-2 text-black">
                                <span class="font-semibold">{{ item.medication?.medication_name || 'Obat' }}</span>
                                <div class="text-[11px] text-black">Dosis: {{ item.dosage || '-' }}</div>
                            </td>
                            <td class="border-r border-black p-2 text-center text-black font-mono">{{ item.qty }}</td>
                            <td class="border-r border-black p-2 text-right font-mono text-black">{{ formatRupiah(item.unit_price) }}</td>
                            <td class="p-2 text-right font-mono font-bold text-black">{{ formatRupiah(item.total_price) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Total -->
                <div class="flex justify-end pt-2">
                    <div class="w-72 space-y-1.5 text-xs">
                        <div class="flex justify-between text-black">
                            <span>Subtotal Pemeriksaan:</span>
                            <span class="font-mono">{{ formatRupiah(invoice.examination_fee) }}</span>
                        </div>
                        <div class="flex justify-between text-black">
                            <span>Subtotal Resep Obat:</span>
                            <span class="font-mono">{{ formatRupiah(invoice.medication_fee) }}</span>
                        </div>
                        <div class="border-t-2 border-black pt-1.5 flex justify-between items-center text-xs">
                            <span class="font-bold text-black uppercase">Total Tagihan:</span>
                            <span class="font-black font-mono text-base text-black">{{ formatRupiah(invoice.total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tanda Tangan Kasir -->
            <div class="border-t border-black pt-6 mt-4 grid grid-cols-2 text-center text-xs text-black">
                <div>
                    <p class="font-semibold">Pasien</p>
                    <div class="h-14"></div>
                    <p class="font-bold">({{ invoice.medical_record?.patient?.patient_name }})</p>
                </div>
                <div>
                    <p class="font-semibold">Petugas Kasir</p>
                    <div class="h-14"></div>
                    <p class="font-bold">( ........................................ )</p>
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
