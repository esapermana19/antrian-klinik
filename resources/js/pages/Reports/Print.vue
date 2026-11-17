<script setup>
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Printer, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    records: Array,
    summary: Object,
    filters: Object,
    clinic: Object,
});

const printNow = () => {
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
            month: 'long',
            day: 'numeric',
        });
    } catch {
        return dateStr;
    }
};

const todayFormatted = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'long',
}).format(new Date());

onMounted(() => {
    // Beri jeda kecil agar font dan gambar ter-render sempurna sebelum prompt cetak
    setTimeout(() => {
        window.print();
    }, 400);
});
</script>

<template>
    <Head title="Cetak Rekap Laporan Operasional Klinik" />

    <div
        class="print-page-wrapper w-full bg-slate-100 p-4 font-sans text-black md:p-8 print:m-0 print:bg-white print:p-0"
    >
        <!-- Floating Action Toolbar (Hanya Tampil di Layar, Tersembunyi saat Cetak) -->
        <div
            class="mx-auto mb-4 flex max-w-6xl items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-white p-3 shadow-xs print:hidden"
        >
            <Link href="/reports">
                <Button
                    variant="outline"
                    size="sm"
                    class="h-8 cursor-pointer gap-1.5 border-slate-200 text-xs text-slate-700 hover:bg-slate-50"
                >
                    <ArrowLeft class="size-3.5" />
                    Kembali ke Laporan
                </Button>
            </Link>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500"
                    >Pilih orientasi <strong>Landscape</strong> pada dialog
                    printer browser.</span
                >
                <Button
                    size="sm"
                    class="h-8 cursor-pointer gap-1.5 bg-slate-900 text-xs font-medium text-white shadow-xs hover:bg-black"
                    @click="printNow"
                >
                    <Printer class="size-3.5" />
                    Cetak Sekarang
                </Button>
            </div>
        </div>

        <!-- Lembar Kertas Resmi Laporan (Standar Dokumen A4 Full Width) -->
        <div
            class="print-sheet mx-auto max-w-6xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:p-8 print:m-0 print:w-full print:max-w-none print:border-none print:p-0 print:shadow-none"
        >
            <!-- Kop Surat Resmi Klinik -->
            <div class="mb-3 border-b-2 border-black pb-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="clinic?.clinic_logo_url"
                            :src="clinic.clinic_logo_url"
                            alt="Logo Klinik"
                            class="h-12 w-auto max-w-[120px] shrink-0 object-contain"
                        />
                        <div>
                            <h1
                                class="text-base leading-tight font-black tracking-wider text-black uppercase"
                            >
                                {{
                                    clinic?.clinic_name ||
                                    'KLINIK PRATAMA SEHAT'
                                }}
                            </h1>
                            <p
                                class="mt-0.5 text-[11px] leading-snug text-black"
                            >
                                {{ clinic?.clinic_address }}
                                <span v-if="clinic?.clinic_phone"
                                    >• Telp: {{ clinic.clinic_phone }}</span
                                >
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <h2
                            class="text-xs font-black tracking-widest text-black uppercase"
                        >
                            REKAPITULASI LAPORAN
                        </h2>
                        <p class="mt-0.5 text-[11px] text-black">
                            Operasional & Keuangan
                        </p>
                    </div>
                </div>
            </div>

            <!-- Metadata Periode & Filter -->
            <div
                class="mb-3 grid grid-cols-2 gap-4 border-b border-black py-1.5 text-[11px]"
            >
                <div class="space-y-0.5">
                    <p class="text-black">
                        <strong>Periode:</strong>
                        {{ formatDate(filters.start_date) }} s/d
                        {{ formatDate(filters.end_date) }}
                    </p>
                    <p class="text-black">
                        <strong>Dokter / Poli:</strong>
                        {{
                            filters.doctor_name
                                ? `${filters.doctor_name} (${filters.specialization || 'Poli'})`
                                : 'Semua Dokter & Poli'
                        }}
                    </p>
                </div>
                <div class="space-y-0.5 text-right">
                    <p class="text-black">
                        <strong>Tanggal Cetak:</strong> {{ todayFormatted }}
                    </p>
                    <p class="text-black">
                        <strong>Total:</strong> {{ records.length }} Kunjungan
                    </p>
                </div>
            </div>

            <!-- Ringkasan Finansial Singkat (Tabel Kompak) -->
            <div class="mb-3">
                <table
                    class="w-full table-fixed border-collapse border border-black text-center text-[11px]"
                >
                    <thead>
                        <tr class="bg-slate-100 print:bg-slate-100">
                            <th
                                class="w-1/5 border border-black p-1.5 font-bold uppercase"
                            >
                                Total Kunjungan
                            </th>
                            <th
                                class="w-1/5 border border-black p-1.5 font-bold uppercase"
                            >
                                Selesai Dilayani
                            </th>
                            <th
                                class="w-1/5 border border-black p-1.5 font-bold uppercase"
                            >
                                Total Jasa Dokter
                            </th>
                            <th
                                class="w-1/5 border border-black p-1.5 font-bold uppercase"
                            >
                                Total Biaya Obat
                            </th>
                            <th
                                class="w-1/5 border border-black bg-slate-200 p-1.5 font-bold uppercase print:bg-slate-200"
                            >
                                Total Pendapatan
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td
                                class="border border-black p-1.5 font-mono font-bold"
                            >
                                {{ summary.total_visits }} Pasien
                            </td>
                            <td
                                class="border border-black p-1.5 font-mono font-bold"
                            >
                                {{ summary.completed_visits }} Selesai
                            </td>
                            <td
                                class="border border-black p-1.5 font-mono font-semibold"
                            >
                                {{ formatRupiah(summary.exam_revenue) }}
                            </td>
                            <td
                                class="border border-black p-1.5 font-mono font-semibold"
                            >
                                {{ formatRupiah(summary.med_revenue) }}
                            </td>
                            <td
                                class="border border-black bg-slate-100 p-1.5 font-mono text-xs font-black print:bg-slate-100"
                            >
                                {{ formatRupiah(summary.total_revenue) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tabel Detail Kunjungan & Tagihan -->
            <table
                class="w-full table-fixed border-collapse border border-black text-[10px]"
            >
                <thead>
                    <tr class="bg-slate-100 text-black print:bg-slate-100">
                        <th class="w-7 border border-black p-1 text-center">
                            No
                        </th>
                        <th class="w-20 border border-black p-1 text-left">
                            Tanggal
                        </th>
                        <th class="w-16 border border-black p-1 text-center">
                            No. Antrean
                        </th>
                        <th class="w-36 border border-black p-1 text-left">
                            Pasien
                        </th>
                        <th class="w-32 border border-black p-1 text-left">
                            Dokter / Poli
                        </th>
                        <th class="w-14 border border-black p-1 text-center">
                            Status
                        </th>
                        <th class="w-20 border border-black p-1 text-right">
                            Jasa Dokter
                        </th>
                        <th class="w-20 border border-black p-1 text-right">
                            Biaya Obat
                        </th>
                        <th class="w-22 border border-black p-1 text-right">
                            Total Biaya
                        </th>
                        <th class="w-12 border border-black p-1 text-center">
                            Bayar
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, index) in records"
                        :key="item.queue_id"
                        class="border-b border-black"
                    >
                        <td
                            class="border border-black p-1 text-center font-mono"
                        >
                            {{ index + 1 }}
                        </td>
                        <td
                            class="border border-black p-1 font-mono whitespace-nowrap"
                        >
                            {{ formatDate(item.queue_date) }}
                        </td>
                        <td
                            class="border border-black p-1 text-center font-mono font-bold"
                        >
                            {{ item.queue_number }}
                        </td>
                        <td class="truncate border border-black p-1">
                            <div class="truncate font-bold">
                                {{ item.patient?.patient_name || '-' }}
                            </div>
                            <div class="font-mono text-[9px] text-black">
                                NIK: {{ item.patient?.nik || '-' }}
                            </div>
                        </td>
                        <td class="truncate border border-black p-1">
                            <div class="truncate">
                                {{ item.doctor?.doctor_name || 'Dokter Umum' }}
                            </div>
                            <div class="truncate text-[9px] text-slate-600">
                                {{ item.doctor?.specialization }}
                            </div>
                        </td>
                        <td
                            class="border border-black p-1 text-center text-[9px] font-semibold uppercase"
                        >
                            {{ item.queue_status }}
                        </td>
                        <td
                            class="border border-black p-1 text-right font-mono"
                        >
                            {{
                                item.medical_record?.invoice
                                    ? formatRupiah(
                                          item.medical_record.invoice
                                              .examination_fee,
                                      )
                                    : '-'
                            }}
                        </td>
                        <td
                            class="border border-black p-1 text-right font-mono"
                        >
                            {{
                                item.medical_record?.invoice
                                    ? formatRupiah(
                                          item.medical_record.invoice
                                              .medication_fee,
                                      )
                                    : '-'
                            }}
                        </td>
                        <td
                            class="border border-black p-1 text-right font-mono font-bold"
                        >
                            {{
                                item.medical_record?.invoice
                                    ? formatRupiah(
                                          item.medical_record.invoice
                                              .total_amount,
                                      )
                                    : '-'
                            }}
                        </td>
                        <td
                            class="border border-black p-1 text-center text-[9px] font-bold"
                        >
                            {{
                                item.medical_record?.invoice?.payment_status ===
                                'paid'
                                    ? 'LUNAS'
                                    : item.medical_record?.invoice
                                      ? 'BELUM'
                                      : '-'
                            }}
                        </td>
                    </tr>

                    <tr v-if="records.length === 0">
                        <td
                            colspan="10"
                            class="border border-black p-6 text-center text-slate-500 italic"
                        >
                            Tidak ada data transaksi pada periode ini.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Lembar Tanda Tangan Penanggung Jawab -->
            <div
                class="mt-6 grid break-inside-avoid grid-cols-2 pt-2 text-center text-[11px] text-black"
            >
                <div></div>
                <div class="space-y-0.5">
                    <p>
                        {{
                            clinic?.clinic_address
                                ? clinic.clinic_address.split(',')[1] || 'Kota'
                                : 'Kota'
                        }}, {{ todayFormatted }}
                    </p>
                    <p class="font-semibold">
                        Penanggung Jawab / Kepala Klinik
                    </p>
                    <div class="h-14"></div>
                    <p class="font-bold underline">
                        ( .....................................................
                        )
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* CSS Reset Khusus Halaman Cetak Laporan (Layar Screen & Print) */
html,
body {
    overflow-x: hidden !important;
}

@media print {
    /* Sembunyikan elemen browser yang tidak relevan */
    aside,
    header,
    nav,
    .print\:hidden {
        display: none !important;
    }

    html,
    body {
        background-color: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        min-height: 100% !important;
        overflow: visible !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .print-page-wrapper,
    .print-sheet {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    @page {
        margin: 10mm;
        size: landscape;
    }

    table {
        width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
    }

    thead {
        display: table-header-group !important;
    }

    tbody {
        display: table-row-group !important;
    }

    tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        page-break-after: auto !important;
    }

    td,
    th {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
}
</style>
