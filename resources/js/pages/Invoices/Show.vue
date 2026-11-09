<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell,
} from '@/components/ui/table';
import {
    Printer,
    ArrowLeft,
} from 'lucide-vue-next';

const props = defineProps({
    invoice: Object,
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

    <div class="space-y-4 p-4 md:p-6 max-w-4xl mx-auto">
        <!-- Top Action Bar (Disembunyikan saat cetak) -->
        <div class="flex items-center justify-between print:hidden">
            <Link href="/invoices">
                <Button variant="outline" size="sm" class="h-8 text-xs gap-1.5 text-slate-600">
                    <ArrowLeft class="size-3.5" />
                    Kembali ke Kasir
                </Button>
            </Link>

            <Button
                size="sm"
                class="h-8 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-medium gap-1.5 shadow-2xs"
                @click="printInvoice"
            >
                <Printer class="size-3.5" />
                Cetak Nota
            </Button>
        </div>

        <!-- Tampilan Kertas Nota Sederhana & Rapi -->
        <Card class="p-6 md:p-8 bg-white border border-slate-200/80 shadow-xs rounded-lg print:border-none print:shadow-none print:p-0">
            <!-- Header Nota Klinik -->
            <div class="border-b border-slate-200 pb-4 text-center">
                <h2 class="text-base font-bold tracking-tight text-slate-900">
                    KLINIK PRATAMA SEHAT
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Jl. Kesehatan No. 123, Kota Sejahtera • Telp: (021) 555-0123
                </p>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-700 mt-2">
                    {{ invoice.payment_status === 'paid' ? 'BUKTI PEMBAYARAN (LUNAS)' : 'TAGIHAN BIAYA PEMERIKSAAN' }}
                </p>
            </div>

            <!-- Metadata Pasien & Tagihan -->
            <div class="grid grid-cols-2 gap-4 py-4 text-xs border-b border-slate-100">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-400 uppercase">Pasien</p>
                    <p class="font-bold text-slate-800">{{ invoice.medical_record?.patient?.patient_name }}</p>
                    <p class="text-slate-500 font-mono">NIK: {{ invoice.medical_record?.patient?.nik || '-' }}</p>
                    <p class="text-slate-500">No. Antrean: {{ invoice.medical_record?.queue?.queue_number || '-' }}</p>
                </div>
                <div class="space-y-1 text-right">
                    <p class="text-[11px] font-semibold text-slate-400 uppercase">Informasi Nota</p>
                    <p class="font-bold font-mono text-slate-800">{{ invoice.invoice_code }}</p>
                    <p class="text-slate-500">{{ formatDate(invoice.created_at) }}</p>
                    <p class="text-slate-600">
                        Status: <span class="font-semibold uppercase" :class="invoice.payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-600'">{{ invoice.payment_status }}</span>
                        <span v-if="invoice.payment_status === 'paid'"> ({{ invoice.payment_method === 'cash' ? 'Tunai' : 'Transfer' }})</span>
                    </p>
                </div>
            </div>

            <!-- Tabel Rincian -->
            <div class="py-4 space-y-3">
                <div class="border border-slate-200/80 rounded-md overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-slate-50/70">
                                <TableHead class="text-xs font-semibold text-slate-700">Uraian Layanan / Resep</TableHead>
                                <TableHead class="text-xs font-semibold text-center w-20">Qty</TableHead>
                                <TableHead class="text-xs font-semibold text-right w-32">Harga Satuan</TableHead>
                                <TableHead class="text-xs font-semibold text-right w-36 pr-4">Subtotal</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <!-- Pemeriksaan Dokter -->
                            <TableRow>
                                <TableCell class="text-xs text-slate-800 py-2">
                                    <span class="font-medium">Jasa Pemeriksaan & Konsultasi Dokter</span>
                                    <div class="text-[11px] text-slate-400">Diagnosis: {{ invoice.medical_record?.diagnosis || '-' }}</div>
                                </TableCell>
                                <TableCell class="text-xs text-center text-slate-600 py-2">1</TableCell>
                                <TableCell class="text-xs text-right font-mono text-slate-600 py-2">{{ formatRupiah(invoice.examination_fee) }}</TableCell>
                                <TableCell class="text-xs text-right font-mono font-medium text-slate-800 py-2 pr-4">{{ formatRupiah(invoice.examination_fee) }}</TableCell>
                            </TableRow>

                            <!-- Daftar Obat -->
                            <TableRow
                                v-for="item in invoice.medical_record?.medication_prescriptions"
                                :key="item.prescription_id"
                            >
                                <TableCell class="text-xs text-slate-800 py-2">
                                    <span class="font-medium">{{ item.medication?.medication_name || 'Obat' }}</span>
                                    <div class="text-[11px] text-slate-400">Dosis: {{ item.dosage || '-' }}</div>
                                </TableCell>
                                <TableCell class="text-xs text-center text-slate-600 py-2">{{ item.qty }}</TableCell>
                                <TableCell class="text-xs text-right font-mono text-slate-600 py-2">{{ formatRupiah(item.unit_price) }}</TableCell>
                                <TableCell class="text-xs text-right font-mono font-medium text-slate-800 py-2 pr-4">{{ formatRupiah(item.total_price) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Total -->
                <div class="flex justify-end pt-2">
                    <div class="w-64 space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal Pemeriksaan:</span>
                            <span class="font-mono text-slate-700">{{ formatRupiah(invoice.examination_fee) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal Resep Obat:</span>
                            <span class="font-mono text-slate-700">{{ formatRupiah(invoice.medication_fee) }}</span>
                        </div>
                        <div class="border-t border-slate-200 pt-1.5 flex justify-between items-center text-xs">
                            <span class="font-bold text-slate-800">Total Tagihan:</span>
                            <span class="font-bold font-mono text-sm text-indigo-600">{{ formatRupiah(invoice.total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tanda Tangan Kasir -->
            <div class="border-t border-slate-200 pt-6 mt-2 grid grid-cols-2 text-center text-xs text-slate-500">
                <div>
                    <p>Pasien</p>
                    <div class="h-12"></div>
                    <p class="font-medium text-slate-700">({{ invoice.medical_record?.patient?.patient_name }})</p>
                </div>
                <div>
                    <p>Petugas Kasir</p>
                    <div class="h-12"></div>
                    <p class="font-medium text-slate-700">( ........................................ )</p>
                </div>
            </div>
        </Card>
    </div>
</template>

<style scoped>
@media print {
    body {
        background: white !important;
        color: black !important;
    }
}
</style>
