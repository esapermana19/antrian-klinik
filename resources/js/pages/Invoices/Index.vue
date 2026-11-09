<script setup>
import { ref } from 'vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
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
    Search,
    ChevronLeft,
    ChevronRight,
    FileText,
    CreditCard,
    X,
} from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import MotionModal from '@/components/MotionModal.vue';

const props = defineProps({
    invoices: Object,
    filters: Object,
    stats: Object,
    errors: Object,
});

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
let searchTimeout = null;

const applyFilters = () => {
    const params = {};
    if (searchQuery.value) {
        params.search = searchQuery.value;
    }
    if (statusFilter.value && statusFilter.value !== 'all') {
        params.status = statusFilter.value;
    }
    router.get('/invoices', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const handleStatusChange = (val) => {
    statusFilter.value = val;
    applyFilters();
};

// Modal Pembayaran Kasir
const isPayModalOpen = ref(false);
const activeInvoice = ref(null);
const payForm = useForm({
    payment_method: 'cash',
});

const openPayModal = (invoice) => {
    activeInvoice.value = invoice;
    payForm.payment_method = 'cash';
    isPayModalOpen.value = true;
};

const closePayModal = () => {
    isPayModalOpen.value = false;
    activeInvoice.value = null;
    payForm.reset();
};

const submitPayment = () => {
    if (!activeInvoice.value) return;
    payForm.patch(`/invoices/${activeInvoice.value.invoice_id}/pay`, {
        onSuccess: () => {
            closePayModal();
        },
    });
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
    <Head title="Kasir & Tagihan" />

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Top Action / Header Area -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Kasir & Tagihan Pembayaran</h1>
                <p class="text-xs text-slate-500">Kelola dan selesaikan transaksi pembayaran pasien klinik serta cetak bukti pembayaran.</p>
            </div>

            <!-- Toolbar Pencarian & Filter Status Compact -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Filter Status -->
                <div class="w-40">
                    <Select :model-value="statusFilter" @update:model-value="handleStatusChange">
                        <SelectTrigger class="w-full h-8 text-xs bg-white/80 border-slate-200/80 shadow-2xs">
                            <SelectValue placeholder="Semua Status" />
                        </SelectTrigger>
                        <SelectContent class="bg-white border-slate-200">
                            <SelectItem value="all" class="text-xs font-medium">Semua Status</SelectItem>
                            <SelectItem value="unpaid" class="text-xs">Belum Lunas</SelectItem>
                            <SelectItem value="paid" class="text-xs">Lunas</SelectItem>
                            <SelectItem value="canceled" class="text-xs">Dibatalkan</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Input Pencarian -->
                <div class="relative w-56">
                    <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 size-3.5 text-slate-400" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="handleSearch"
                        placeholder="Cari kode/pasien/NIK..."
                        class="w-full h-8 pl-8 pr-3 text-xs rounded-lg border border-slate-200/80 bg-white/80 focus:bg-white focus:border-indigo-500 focus:outline-none transition shadow-2xs"
                    />
                </div>
            </div>
        </div>

        <!-- Clean Standard Table (Konsisten dengan menu Pasien & Obat) -->
        <Card class="p-0 overflow-hidden border border-slate-200/80 bg-white shadow-xs rounded-lg">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12 text-center">No</TableHead>
                        <TableHead class="w-36">Kode Invoice</TableHead>
                        <TableHead>Pasien</TableHead>
                        <TableHead class="w-24 text-center">No. Antrean</TableHead>
                        <TableHead class="w-32 text-right">Biaya Dokter</TableHead>
                        <TableHead class="w-32 text-right">Biaya Obat</TableHead>
                        <TableHead class="w-32 text-right">Total Biaya</TableHead>
                        <TableHead class="w-28 text-center">Status</TableHead>
                        <TableHead class="text-right pr-4 w-32">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(item, index) in invoices.data"
                        :key="item.invoice_id"
                    >
                        <!-- No -->
                        <TableCell class="text-center text-slate-400 font-mono text-[11px]">
                            {{ (invoices.current_page - 1) * invoices.per_page + (index + 1) }}
                        </TableCell>

                        <!-- Kode Invoice -->
                        <TableCell class="font-mono text-xs font-medium text-slate-700">
                            {{ item.invoice_code }}
                            <div class="text-[10px] font-sans text-slate-400">
                                {{ formatDate(item.created_at) }}
                            </div>
                        </TableCell>

                        <!-- Pasien -->
                        <TableCell>
                            <div class="font-medium text-xs text-slate-900">
                                {{ item.medical_record?.patient?.patient_name || '-' }}
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                {{ item.medical_record?.patient?.nik || '-' }}
                            </div>
                        </TableCell>

                        <!-- No. Antrean -->
                        <TableCell class="text-center font-mono text-xs text-slate-700">
                            {{ item.medical_record?.queue?.queue_number || '-' }}
                        </TableCell>

                        <!-- Biaya Dokter -->
                        <TableCell class="text-right text-xs font-mono text-slate-600">
                            {{ formatRupiah(item.examination_fee) }}
                        </TableCell>

                        <!-- Biaya Obat -->
                        <TableCell class="text-right text-xs font-mono text-slate-600">
                            {{ formatRupiah(item.medication_fee) }}
                        </TableCell>

                        <!-- Total Tagihan -->
                        <TableCell class="text-right text-xs font-mono font-semibold text-slate-900">
                            {{ formatRupiah(item.total_amount) }}
                        </TableCell>

                        <!-- Status Sederhana (Text/Simple Clean Badge) -->
                        <TableCell class="text-center text-xs">
                            <span
                                v-if="item.payment_status === 'paid'"
                                class="text-emerald-700 font-medium"
                            >
                                Lunas ({{ item.payment_method === 'cash' ? 'Tunai' : 'Transfer' }})
                            </span>
                            <span
                                v-else-if="item.payment_status === 'unpaid'"
                                class="text-amber-600 font-medium"
                            >
                                Belum Bayar
                            </span>
                            <span
                                v-else
                                class="text-slate-400 font-medium"
                            >
                                Batal
                            </span>
                        </TableCell>

                        <!-- Aksi Tombol -->
                        <TableCell class="text-right pr-4">
                            <div class="flex items-center justify-end gap-1.5">
                                <Button
                                    v-if="item.payment_status === 'unpaid'"
                                    size="sm"
                                    class="h-7 px-2.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-2xs gap-1"
                                    @click="openPayModal(item)"
                                >
                                    Bayar
                                </Button>

                                <Link :href="`/invoices/${item.invoice_id}`">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-7 px-2 text-xs text-slate-600 hover:text-slate-900 border-slate-200"
                                        title="Lihat Struk"
                                    >
                                        <FileText class="size-3" />
                                        Nota
                                    </Button>
                                </Link>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="!invoices.data || invoices.data.length === 0" colspan="9">
                        <div class="py-10 text-center text-slate-400 text-xs">
                            Belum ada data tagihan kasir.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Standard Compact Pagination (Identik dengan Patient & Medication Index) -->
            <div
                v-if="invoices.links && invoices.links.length > 3"
                class="px-4 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 bg-slate-50/50"
            >
                <div>
                    Menampilkan <span class="font-medium text-slate-700">{{ invoices.from || 0 }}</span> -
                    <span class="font-medium text-slate-700">{{ invoices.to || 0 }}</span> dari
                    <span class="font-medium text-slate-700">{{ invoices.total }}</span> tagihan
                </div>
                <div class="flex items-center gap-1">
                    <template v-for="(link, i) in invoices.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="h-7 min-w-7 px-2 flex items-center justify-center rounded text-xs transition"
                            :class="link.active ? 'bg-indigo-600 text-white font-medium' : 'hover:bg-slate-200 text-slate-600'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="h-7 min-w-7 px-2 flex items-center justify-center rounded text-xs text-slate-300 cursor-not-allowed"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </Card>

        <!-- Modal Sederhana Pembayaran Kasir -->
        <MotionModal :show="isPayModalOpen" max-width="max-w-sm" @close="closePayModal">
            <div class="p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Pembayaran Kasir</h3>
                        <p class="text-[11px] font-mono text-slate-400">{{ activeInvoice?.invoice_code }}</p>
                    </div>
                    <button
                        type="button"
                        class="p-1 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                        @click="closePayModal"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <!-- Info Rincian -->
                <div class="bg-slate-50 rounded-lg p-3 space-y-1.5 text-xs border border-slate-100">
                    <div class="flex justify-between text-slate-500">
                        <span>Nama Pasien</span>
                        <span class="font-medium text-slate-800">{{ activeInvoice?.medical_record?.patient?.patient_name }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Biaya Dokter</span>
                        <span class="font-mono text-slate-800">{{ formatRupiah(activeInvoice?.examination_fee) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Biaya Obat</span>
                        <span class="font-mono text-slate-800">{{ formatRupiah(activeInvoice?.medication_fee) }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-1.5 flex justify-between items-center text-xs">
                        <span class="font-semibold text-slate-800">Total Tagihan</span>
                        <span class="text-sm font-bold font-mono text-indigo-600">{{ formatRupiah(activeInvoice?.total_amount) }}</span>
                    </div>
                </div>

                <!-- Pilih Metode Pembayaran -->
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-700">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label
                            class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg border text-xs font-medium cursor-pointer transition"
                            :class="payForm.payment_method === 'cash' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                        >
                            <input
                                v-model="payForm.payment_method"
                                type="radio"
                                value="cash"
                                class="sr-only"
                            />
                            Tunai (Cash)
                        </label>

                        <label
                            class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg border text-xs font-medium cursor-pointer transition"
                            :class="payForm.payment_method === 'transfer' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                        >
                            <input
                                v-model="payForm.payment_method"
                                type="radio"
                                value="transfer"
                                class="sr-only"
                            />
                            Transfer / QRIS
                        </label>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <Button variant="outline" size="sm" class="h-8 text-xs" type="button" @click="closePayModal">
                        Batal
                    </Button>
                    <Button
                        size="sm"
                        class="h-8 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-medium"
                        :disabled="payForm.processing"
                        @click="submitPayment"
                    >
                        {{ payForm.processing ? 'Memproses...' : 'Selesaikan Pembayaran' }}
                    </Button>
                </div>
            </div>
        </MotionModal>
    </div>
</template>
