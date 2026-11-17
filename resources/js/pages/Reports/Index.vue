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
        { preserveState: true, preserveScroll: true, replace: true },
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

    <div class="max-w-7xl space-y-4 p-4 md:p-6">
        <!-- Top Action / Header Area -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden"
        >
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">
                    Laporan Operasional & Keuangan
                </h1>
                <p class="text-xs text-slate-500">
                    Rekapitulasi kunjungan pasien, efektivitas antrean, dan
                    rincian pendapatan klinik.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Button
                    size="sm"
                    variant="outline"
                    class="cursor-pointer gap-1.5 border-slate-200 bg-white text-slate-700 shadow-2xs hover:bg-slate-50"
                    @click="printReport"
                >
                    <Printer class="size-3.5 text-slate-500" />
                    Cetak Laporan
                </Button>
                <Button
                    size="sm"
                    class="cursor-pointer gap-1.5 bg-emerald-600 font-medium text-white shadow-xs hover:bg-emerald-700"
                    @click="handleExportExcel"
                >
                    <FileSpreadsheet class="size-3.5" />
                    Export Excel (.xlsx)
                </Button>
            </div>
        </div>

        <!-- Filter Bar -->
        <Card
            class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-2xs print:hidden"
        >
            <div class="flex flex-wrap items-center gap-3">
                <!-- Filter Tanggal Mulai -->
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] font-medium text-slate-500"
                        >Dari:</label
                    >
                    <input
                        type="date"
                        v-model="startDate"
                        @change="handleFilter"
                        class="h-8 cursor-pointer rounded-lg border border-slate-200 bg-white px-2 font-mono text-xs text-slate-700 focus:border-indigo-500 focus:outline-none"
                    />
                </div>

                <!-- Filter Tanggal Selesai -->
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] font-medium text-slate-500"
                        >Sampai:</label
                    >
                    <input
                        type="date"
                        v-model="endDate"
                        @change="handleFilter"
                        class="h-8 cursor-pointer rounded-lg border border-slate-200 bg-white px-2 font-mono text-xs text-slate-700 focus:border-indigo-500 focus:outline-none"
                    />
                </div>

                <!-- Filter Dokter -->
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] font-medium text-slate-500"
                        >Dokter/Poli:</label
                    >
                    <select
                        v-model="doctorId"
                        @change="handleFilter"
                        class="h-8 cursor-pointer rounded-lg border border-slate-200 bg-white px-2.5 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none"
                    >
                        <option value="">Semua Dokter & Poli</option>
                        <option
                            v-for="doc in doctors"
                            :key="doc.doctor_id"
                            :value="doc.doctor_id"
                        >
                            {{ doc.doctor_name }} ({{ doc.specialization }})
                        </option>
                    </select>
                </div>
            </div>
        </Card>

        <!-- Ringkasan Statistik (Summary Cards) -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4 print:grid-cols-4">
            <!-- Total Pasien -->
            <Card
                class="space-y-1 rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-xs"
            >
                <span
                    class="flex items-center justify-between text-[11px] font-medium tracking-wider text-slate-500 uppercase"
                >
                    Total Kunjungan
                    <Users class="size-3.5 text-indigo-500" />
                </span>
                <div
                    class="font-mono text-xl font-bold tracking-tight text-slate-900"
                >
                    {{ summary.total_visits }}
                    <span class="font-sans text-xs font-normal text-slate-500"
                        >Pasien</span
                    >
                </div>
                <p class="text-[10px] text-slate-400">
                    {{ summary.completed_visits }} selesai •
                    {{ summary.canceled_visits }} batal
                </p>
            </Card>

            <!-- Total Pendapatan -->
            <Card
                class="space-y-1 rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-xs"
            >
                <span
                    class="flex items-center justify-between text-[11px] font-medium tracking-wider text-slate-500 uppercase"
                >
                    Total Pendapatan
                    <DollarSign class="size-3.5 text-emerald-500" />
                </span>
                <div
                    class="font-mono text-xl font-bold tracking-tight text-emerald-600"
                >
                    {{ formatRupiah(summary.total_revenue) }}
                </div>
                <p class="text-[10px] font-medium text-emerald-600/80">
                    Status tagihan lunas
                </p>
            </Card>

            <!-- Biaya Jasa Dokter -->
            <Card
                class="space-y-1 rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-xs"
            >
                <span
                    class="flex items-center justify-between text-[11px] font-medium tracking-wider text-slate-500 uppercase"
                >
                    Jasa Pemeriksaan
                    <Stethoscope class="size-3.5 text-blue-500" />
                </span>
                <div
                    class="font-mono text-xl font-bold tracking-tight text-slate-800"
                >
                    {{ formatRupiah(summary.exam_revenue) }}
                </div>
                <p class="text-[10px] text-slate-400">
                    Tarif tindakan & periksa
                </p>
            </Card>

            <!-- Biaya Farmasi Obat -->
            <Card
                class="space-y-1 rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-xs"
            >
                <span
                    class="flex items-center justify-between text-[11px] font-medium tracking-wider text-slate-500 uppercase"
                >
                    Pendapatan Obat
                    <Pill class="size-3.5 text-amber-500" />
                </span>
                <div
                    class="font-mono text-xl font-bold tracking-tight text-slate-800"
                >
                    {{ formatRupiah(summary.med_revenue) }}
                </div>
                <p class="text-[10px] text-slate-400">
                    Resep & penjualan farmasi
                </p>
            </Card>
        </div>

        <!-- Tabel Rincian Kunjungan & Tagihan -->
        <Card
            class="overflow-hidden rounded-xl border border-slate-200/80 bg-white p-0 shadow-xs"
        >
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
                        <TableHead class="w-28 pr-4 text-center"
                            >Pembayaran</TableHead
                        >
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(item, index) in records.data"
                        :key="item.queue_id"
                    >
                        <!-- No -->
                        <TableCell
                            class="text-center font-mono text-[11px] text-slate-400"
                        >
                            {{
                                (records.current_page - 1) * records.per_page +
                                (index + 1)
                            }}
                        </TableCell>

                        <!-- Tanggal -->
                        <TableCell
                            class="font-mono text-xs whitespace-nowrap text-slate-600"
                        >
                            {{ formatDate(item.queue_date) }}
                        </TableCell>

                        <!-- No Antrean -->
                        <TableCell
                            class="font-mono text-xs font-semibold text-slate-800"
                        >
                            {{ item.queue_number }}
                        </TableCell>

                        <!-- Pasien -->
                        <TableCell>
                            <div class="text-xs font-medium text-slate-900">
                                {{ item.patient?.patient_name }}
                            </div>
                            <div class="font-mono text-[11px] text-slate-400">
                                NIK: {{ item.patient?.nik || '-' }}
                            </div>
                        </TableCell>

                        <!-- Dokter -->
                        <TableCell class="text-xs text-slate-700">
                            <div class="font-medium">
                                {{ item.doctor?.doctor_name || 'Dokter Umum' }}
                            </div>
                            <div
                                v-if="item.doctor?.specialization"
                                class="text-[11px] text-slate-500"
                            >
                                {{ item.doctor.specialization }}
                            </div>
                        </TableCell>

                        <!-- Status Antrean -->
                        <TableCell class="text-center">
                            <span
                                v-if="item.queue_status === 'completed'"
                                class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-emerald-600"
                                ></span>
                                Selesai
                            </span>
                            <span
                                v-else-if="item.queue_status === 'cancel'"
                                class="inline-flex items-center gap-1 text-xs font-medium text-rose-700"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-rose-600"
                                ></span>
                                Batal
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 text-xs font-medium text-slate-500"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-slate-400"
                                ></span>
                                {{ item.queue_status }}
                            </span>
                        </TableCell>

                        <!-- Total Tagihan -->
                        <TableCell
                            class="text-right font-mono text-xs font-semibold text-slate-800"
                        >
                            {{
                                item.medical_record?.invoice
                                    ? formatRupiah(
                                          item.medical_record.invoice
                                              .total_amount,
                                      )
                                    : '-'
                            }}
                        </TableCell>

                        <!-- Status Pembayaran -->
                        <TableCell class="pr-4 text-center">
                            <span
                                v-if="
                                    item.medical_record?.invoice
                                        ?.payment_status === 'paid'
                                "
                                class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-emerald-600"
                                ></span>
                                Lunas
                            </span>
                            <span
                                v-else-if="item.medical_record?.invoice"
                                class="inline-flex items-center gap-1 text-xs font-medium text-amber-700"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-amber-600"
                                ></span>
                                Belum
                            </span>
                            <span v-else class="text-xs text-slate-400">-</span>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="records.data.length === 0" :colspan="8">
                        <div class="py-10 text-center text-xs text-slate-400">
                            Tidak ada catatan kunjungan atau transaksi pada
                            periode ini.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Pagination Bar -->
            <div
                class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-4 py-2.5 text-xs text-slate-500 print:hidden"
            >
                <span>
                    Hal
                    <strong class="text-slate-700">{{
                        records.current_page
                    }}</strong>
                    dari {{ records.last_page }} (Total: {{ records.total }})
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!records.prev_page_url"
                        as-child
                    >
                        <Link
                            v-if="records.prev_page_url"
                            :href="records.prev_page_url"
                        >
                            <ChevronLeft class="size-3" /> Prev
                        </Link>
                        <span v-else class="flex items-center opacity-40">
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
                        <Link
                            v-if="records.next_page_url"
                            :href="records.next_page_url"
                        >
                            Next <ChevronRight class="size-3" />
                        </Link>
                        <span v-else class="flex items-center opacity-40">
                            Next <ChevronRight class="size-3" />
                        </span>
                    </Button>
                </div>
            </div>
        </Card>
    </div>
</template>
