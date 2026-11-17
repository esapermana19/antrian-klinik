<script setup>
import { ref } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell,
    TableEmpty,
} from '@/components/ui/table';
import {
    FileSpreadsheet,
    Printer,
    ChevronLeft,
    ChevronRight,
    Calendar,
    Users,
    CheckCircle2,
    DollarSign,
    Pill,
    Stethoscope,
    Clock,
} from 'lucide-vue-next';

const props = defineProps({
    records: Object,
    doctors: Array,
    summary: Object,
    filters: Object,
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');
const doctorId = ref(props.filters?.doctor_id || '');

const handleFilter = () => {
    router.get(
        '/reports',
        {
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            doctor_id: doctorId.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const handleExportExcel = () => {
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    if (doctorId.value) params.append('doctor_id', doctorId.value);
    window.location.href = `/reports/export-excel?${params.toString()}`;
};

const printReport = () => {
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    if (doctorId.value) params.append('doctor_id', doctorId.value);
    window.open(`/reports/print?${params.toString()}`, '_blank');
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
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <Head title="Laporan Kunjungan & Pendapatan Klinik" />

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Top Action / Header Area -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Laporan Operasional & Keuangan</h1>
                <p class="text-xs text-slate-500">Rekapitulasi kunjungan pasien, efektivitas antrean, dan rincian pendapatan klinik.</p>
            </div>
            <div class="flex items-center gap-2">
                <Button size="sm" variant="outline" class="gap-1.5 border-slate-200 text-slate-700 bg-white shadow-2xs hover:bg-slate-50 cursor-pointer" @click="printReport">
                    <Printer class="size-3.5 text-slate-500" />
                    Cetak Laporan
                </Button>
                <Button size="sm" class="gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow-xs cursor-pointer" @click="handleExportExcel">
                    <FileSpreadsheet class="size-3.5" />
                    Export Excel (.xlsx)
                </Button>
            </div>
        </div>

        <!-- Filter Bar -->
        <Card class="p-3 border border-slate-200/80 bg-white shadow-2xs rounded-xl print:hidden">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Filter Tanggal Mulai -->
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] font-medium text-slate-500">Dari:</label>
                    <input
                        type="date"
                        v-model="startDate"
                        @change="handleFilter"
                        class="h-8 px-2 text-xs font-mono rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-indigo-500 cursor-pointer"
                    />
                </div>

                <!-- Filter Tanggal Selesai -->
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] font-medium text-slate-500">Sampai:</label>
                    <input
                        type="date"
                        v-model="endDate"
                        @change="handleFilter"
                        class="h-8 px-2 text-xs font-mono rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-indigo-500 cursor-pointer"
                    />
                </div>

                <!-- Filter Dokter -->
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] font-medium text-slate-500">Dokter/Poli:</label>
                    <select
                        v-model="doctorId"
                        @change="handleFilter"
                        class="h-8 px-2.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-indigo-500 cursor-pointer"
                    >
                        <option value="">Semua Dokter & Poli</option>
                        <option v-for="doc in doctors" :key="doc.doctor_id" :value="doc.doctor_id">
                            {{ doc.doctor_name }} ({{ doc.specialization }})
                        </option>
                    </select>
                </div>
            </div>
        </Card>

        <!-- Ringkasan Statistik (Summary Cards) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 print:grid-cols-4">
            <!-- Total Pasien -->
            <Card class="p-3.5 border border-slate-200/80 bg-white shadow-xs rounded-xl space-y-1">
                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider flex items-center justify-between">
                    Total Kunjungan
                    <Users class="size-3.5 text-indigo-500" />
                </span>
                <div class="text-xl font-bold font-mono text-slate-900 tracking-tight">
                    {{ summary.total_visits }} <span class="text-xs font-normal text-slate-500 font-sans">Pasien</span>
                </div>
                <p class="text-[10px] text-slate-400">
                    {{ summary.completed_visits }} selesai • {{ summary.canceled_visits }} batal
                </p>
            </Card>

            <!-- Total Pendapatan -->
            <Card class="p-3.5 border border-slate-200/80 bg-white shadow-xs rounded-xl space-y-1">
                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider flex items-center justify-between">
                    Total Pendapatan
                    <DollarSign class="size-3.5 text-emerald-500" />
                </span>
                <div class="text-xl font-bold font-mono text-emerald-600 tracking-tight">
                    {{ formatRupiah(summary.total_revenue) }}
                </div>
                <p class="text-[10px] text-emerald-600/80 font-medium">
                    Status tagihan lunas
                </p>
            </Card>

            <!-- Biaya Jasa Dokter -->
            <Card class="p-3.5 border border-slate-200/80 bg-white shadow-xs rounded-xl space-y-1">
                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider flex items-center justify-between">
                    Jasa Pemeriksaan
                    <Stethoscope class="size-3.5 text-blue-500" />
                </span>
                <div class="text-xl font-bold font-mono text-slate-800 tracking-tight">
                    {{ formatRupiah(summary.exam_revenue) }}
                </div>
                <p class="text-[10px] text-slate-400">
                    Tarif tindakan & periksa
                </p>
            </Card>

            <!-- Biaya Farmasi Obat -->
            <Card class="p-3.5 border border-slate-200/80 bg-white shadow-xs rounded-xl space-y-1">
                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider flex items-center justify-between">
                    Pendapatan Obat
                    <Pill class="size-3.5 text-amber-500" />
                </span>
                <div class="text-xl font-bold font-mono text-slate-800 tracking-tight">
                    {{ formatRupiah(summary.med_revenue) }}
                </div>
                <p class="text-[10px] text-slate-400">
                    Resep & penjualan farmasi
                </p>
            </Card>
        </div>

        <!-- Tabel Rincian Kunjungan & Tagihan -->
        <Card class="p-0 overflow-hidden border border-slate-200/80 bg-white shadow-xs rounded-xl">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12 text-center">No</TableHead>
                        <TableHead class="w-28">Tanggal</TableHead>
                        <TableHead class="w-32">No. Antrean</TableHead>
                        <TableHead>Pasien</TableHead>
                        <TableHead class="w-44">Dokter / Poli</TableHead>
                        <TableHead class="w-28 text-center">Status</TableHead>
                        <TableHead class="w-36 text-right">Tagihan</TableHead>
                        <TableHead class="w-28 text-center pr-4">Pembayaran</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(item, index) in records.data"
                        :key="item.queue_id"
                    >
                        <!-- No -->
                        <TableCell class="text-center text-slate-400 font-mono text-[11px]">
                            {{ (records.current_page - 1) * records.per_page + (index + 1) }}
                        </TableCell>

                        <!-- Tanggal -->
                        <TableCell class="text-xs text-slate-600 font-mono whitespace-nowrap">
                            {{ formatDate(item.queue_date) }}
                        </TableCell>

                        <!-- No Antrean -->
                        <TableCell class="font-mono text-xs font-semibold text-slate-800">
                            {{ item.queue_number }}
                        </TableCell>

                        <!-- Pasien -->
                        <TableCell>
                            <div class="font-medium text-xs text-slate-900">
                                {{ item.patient?.patient_name }}
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                NIK: {{ item.patient?.nik || '-' }}
                            </div>
                        </TableCell>

                        <!-- Dokter -->
                        <TableCell class="text-xs text-slate-700">
                            <div class="font-medium">{{ item.doctor?.doctor_name || 'Dokter Umum' }}</div>
                            <div v-if="item.doctor?.specialization" class="text-[11px] text-slate-500">{{ item.doctor.specialization }}</div>
                        </TableCell>

                        <!-- Status Antrean -->
                        <TableCell class="text-center">
                            <span v-if="item.queue_status === 'completed'" class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700">
                                <span class="size-1.5 rounded-full bg-emerald-600"></span> Selesai
                            </span>
                            <span v-else-if="item.queue_status === 'cancel'" class="inline-flex items-center gap-1 text-xs font-medium text-rose-700">
                                <span class="size-1.5 rounded-full bg-rose-600"></span> Batal
                            </span>
                            <span v-else class="inline-flex items-center gap-1 text-xs font-medium text-slate-500">
                                <span class="size-1.5 rounded-full bg-slate-400"></span> {{ item.queue_status }}
                            </span>
                        </TableCell>

                        <!-- Total Tagihan -->
                        <TableCell class="text-right font-mono text-xs font-semibold text-slate-800">
                            {{ item.medical_record?.invoice ? formatRupiah(item.medical_record.invoice.total_amount) : '-' }}
                        </TableCell>

                        <!-- Status Pembayaran -->
                        <TableCell class="text-center pr-4">
                            <span
                                v-if="item.medical_record?.invoice?.payment_status === 'paid'"
                                class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700"
                            >
                                <span class="size-1.5 rounded-full bg-emerald-600"></span> Lunas
                            </span>
                            <span
                                v-else-if="item.medical_record?.invoice"
                                class="inline-flex items-center gap-1 text-xs font-medium text-amber-700"
                            >
                                <span class="size-1.5 rounded-full bg-amber-600"></span> Belum
                            </span>
                            <span v-else class="text-xs text-slate-400">-</span>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="records.data.length === 0" :colspan="8">
                        <div class="py-10 text-center text-slate-400 text-xs">
                            Tidak ada catatan kunjungan atau transaksi pada periode ini.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Pagination Bar -->
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-500 print:hidden">
                <span>
                    Hal <strong class="text-slate-700">{{ records.current_page }}</strong> dari {{ records.last_page }} (Total: {{ records.total }})
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!records.prev_page_url"
                        as-child
                    >
                        <Link v-if="records.prev_page_url" :href="records.prev_page_url">
                            <ChevronLeft class="size-3" /> Prev
                        </Link>
                        <span v-else class="opacity-40 flex items-center">
                            <ChevronLeft class="size-3" /> Prev
                        </span>
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!records.next_page_url"
                        as-child
                    >
                        <Link v-if="records.next_page_url" :href="records.next_page_url">
                            Next <ChevronRight class="size-3" />
                        </Link>
                        <span v-else class="opacity-40 flex items-center">
                            Next <ChevronRight class="size-3" />
                        </span>
                    </Button>
                </div>
            </div>
        </Card>
    </div>
</template>
