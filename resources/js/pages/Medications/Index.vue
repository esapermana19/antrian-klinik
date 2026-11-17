<script setup>
import { ref } from 'vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
import medicationRoute from '@/routes/medication';
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
    Plus,
    Pencil,
    Trash2,
    ChevronLeft,
    ChevronRight,
    X,
    Search,
    Filter,
    AlertTriangle,
} from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import MotionModal from '@/components/MotionModal.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';

const props = defineProps({
    medications: Object,
    errors: Object,
    medication_categories: Array,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const selectedCategoryFilter = ref(
    props.filters?.category_id ? String(props.filters.category_id) : 'all',
);
const lowStockFilter = ref(Boolean(props.filters?.low_stock));
let searchTimeout = null;

const applyFilters = () => {
    const params = {};
    if (searchQuery.value) {
        params.search = searchQuery.value;
    }
    if (
        selectedCategoryFilter.value &&
        selectedCategoryFilter.value !== 'all'
    ) {
        params.category_id = selectedCategoryFilter.value;
    }
    if (lowStockFilter.value) {
        params.low_stock = 1;
    }
    router.get('/medication', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const toggleLowStockFilter = () => {
    lowStockFilter.value = !lowStockFilter.value;
    applyFilters();
};

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const handleCategoryFilterChange = (val) => {
    selectedCategoryFilter.value = val;
    applyFilters();
};

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMedicationId = ref(null);

// State untuk Dialog Alert Konfirmasi Hapus Obat
const isDeleteModalOpen = ref(false);
const medicationToDelete = ref(null);
const isDeleting = ref(false);

const form = useForm({
    medication_category_id: '',
    medication_name: '',
    stock: '',
    min_stock: 10,
    unit_price: '',
});

const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (medication) => {
    isEditMode.value = true;
    selectedMedicationId.value = medication.medication_id ?? medication.id;
    form.clearErrors();
    form.medication_category_id =
        medication.medication_category_id ??
        medication.medication_category?.medication_category_id ??
        '';
    form.medication_name = medication.medication_name;
    form.stock = medication.stock;
    form.min_stock = medication.min_stock ?? 10;
    form.unit_price = medication.unit_price;

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(medicationRoute.update.url(selectedMedicationId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(medicationRoute.store.url(), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDeleteMedication = (medication) => {
    medicationToDelete.value = medication;
    isDeleteModalOpen.value = true;
};

const handleDeleteMedication = () => {
    if (!medicationToDelete.value) return;
    const id =
        medicationToDelete.value.medication_id ?? medicationToDelete.value.id;
    isDeleting.value = true;
    router.delete(medicationRoute.destroy.url(id), {
        onFinish: () => {
            isDeleting.value = false;
            isDeleteModalOpen.value = false;
            medicationToDelete.value = null;
        },
    });
};

const formatCurrency = (val) => {
    if (!val && val !== 0) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(val);
};
</script>

<template>
    <Head title="Data Obat & Farmasi" />

    <div class="max-w-7xl space-y-4 p-4 md:p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">
                    Data Obat & Farmasi
                </h1>
                <p class="text-xs text-slate-500">
                    Kelola inventaris obat-obatan, stok persediaan, dan harga
                    satuan klinik.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Filter Kategori Shadcn Select -->
                <div class="w-44">
                    <Select
                        :model-value="selectedCategoryFilter"
                        @update:model-value="handleCategoryFilterChange"
                    >
                        <SelectTrigger
                            class="h-8 w-full border-slate-200/80 bg-white/80 text-xs shadow-2xs"
                        >
                            <SelectValue placeholder="Semua Kategori" />
                        </SelectTrigger>
                        <SelectContent class="border-slate-200 bg-white">
                            <SelectItem value="all" class="text-xs font-medium"
                                >Semua Kategori</SelectItem
                            >
                            <SelectItem
                                v-for="cat in medication_categories"
                                :key="cat.medication_category_id ?? cat.id"
                                :value="
                                    String(cat.medication_category_id ?? cat.id)
                                "
                                class="text-xs"
                            >
                                {{ cat.medication_category_name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Search Input Nama Obat -->
                <div class="relative w-56">
                    <Search
                        class="absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="handleSearch"
                        placeholder="Cari nama obat..."
                        class="h-8 w-full rounded-lg border border-slate-200/80 bg-white/80 pr-3 pl-8 text-xs shadow-2xs transition focus:border-indigo-500 focus:bg-white focus:outline-none"
                    />
                </div>

                <!-- Tombol Filter Stok Menipis -->
                <button
                    type="button"
                    @click="toggleLowStockFilter"
                    class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 text-xs font-medium transition"
                    :class="
                        lowStockFilter
                            ? 'border-rose-300 bg-rose-50 text-rose-700 shadow-2xs'
                            : 'border-slate-200/80 bg-white/80 text-slate-600 shadow-2xs hover:bg-slate-50'
                    "
                    title="Filter obat yang stoknya di bawah batas minimum"
                >
                    <AlertTriangle
                        class="size-3.5"
                        :class="
                            lowStockFilter ? 'text-rose-600' : 'text-slate-400'
                        "
                    />
                    <span>Stok Menipis</span>
                </button>

                <Button
                    size="sm"
                    class="gap-1.5 bg-indigo-600 text-white shadow-xs hover:bg-indigo-700"
                    @click="openAddModal"
                >
                    <Plus class="size-3.5" />
                    Tambah Obat
                </Button>
            </div>
        </div>

        <!-- Clean Standard Table (Tanpa Badge AI) -->
        <Card
            class="overflow-hidden rounded-lg border border-slate-200/80 bg-white p-0 shadow-xs"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12 text-center">No</TableHead>
                        <TableHead>Nama Obat</TableHead>
                        <TableHead class="w-48">Kategori</TableHead>
                        <TableHead class="w-36 text-right"
                            >Stok / Min</TableHead
                        >
                        <TableHead class="w-40 text-right"
                            >Harga Satuan</TableHead
                        >
                        <TableHead class="w-28 pr-4 text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(item, index) in medications.data"
                        :key="item.medication_id ?? item.id"
                        :class="
                            item.stock <= (item.min_stock ?? 10)
                                ? 'bg-rose-50/40 hover:bg-rose-50/60'
                                : ''
                        "
                    >
                        <TableCell
                            class="text-center font-mono text-[11px] text-slate-400"
                        >
                            {{
                                (medications.current_page - 1) *
                                    medications.per_page +
                                (index + 1)
                            }}
                        </TableCell>
                        <TableCell class="text-xs font-medium text-slate-900">
                            <div class="flex items-center gap-2">
                                <span>{{ item.medication_name }}</span>
                                <span
                                    v-if="item.stock <= (item.min_stock ?? 10)"
                                    class="inline-flex items-center gap-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-semibold text-rose-700"
                                    title="Stok menipis, segera restock"
                                >
                                    <span
                                        class="size-1.5 animate-pulse rounded-full bg-rose-500"
                                    ></span>
                                    Restock
                                </span>
                            </div>
                        </TableCell>
                        <TableCell class="text-xs text-slate-600">
                            {{
                                item.medication_category
                                    ?.medication_category_name || '-'
                            }}
                        </TableCell>
                        <TableCell class="text-right font-mono text-xs">
                            <div class="flex items-center justify-end gap-1.5">
                                <span
                                    class="font-semibold"
                                    :class="
                                        item.stock <= (item.min_stock ?? 10)
                                            ? 'text-rose-600'
                                            : 'text-slate-800'
                                    "
                                >
                                    {{ item.stock }}
                                </span>
                                <span class="text-[11px] text-slate-400"
                                    >/ min {{ item.min_stock ?? 10 }}</span
                                >
                            </div>
                        </TableCell>
                        <TableCell
                            class="text-right font-mono text-xs text-slate-700"
                        >
                            {{ formatCurrency(item.unit_price) }}
                        </TableCell>
                        <TableCell class="pr-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 border-slate-200 text-slate-500 hover:text-indigo-600"
                                    @click="openEditModal(item)"
                                    title="Edit"
                                >
                                    <Pencil class="size-3" />
                                </Button>
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 border-slate-200 text-slate-400 hover:border-rose-200 hover:text-rose-600"
                                    @click="confirmDeleteMedication(item)"
                                    title="Hapus"
                                >
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableEmpty
                        v-if="medications.data.length === 0"
                        :colspan="6"
                    >
                        <div class="py-10 text-center text-xs text-slate-400">
                            Belum ada data obat terdaftar.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Pagination Bar -->
            <div
                class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-4 py-2.5 text-xs text-slate-500"
            >
                <span>
                    Hal
                    <strong class="text-slate-700">{{
                        medications.current_page
                    }}</strong>
                    dari {{ medications.last_page }}
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!medications.prev_page_url"
                        as-child
                    >
                        <Link
                            v-if="medications.prev_page_url"
                            :href="medications.prev_page_url"
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
                        :disabled="!medications.next_page_url"
                        as-child
                    >
                        <Link
                            v-if="medications.next_page_url"
                            :href="medications.next_page_url"
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

        <!-- Modal Form Tambah / Edit (Physics Spring Motion + Shadcn Select) -->
        <MotionModal
            :show="isModalOpen"
            max-width="max-w-md"
            @close="closeModal"
        >
            <div
                class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3"
            >
                <h2 class="text-sm font-bold text-slate-900">
                    {{ isEditMode ? 'Edit Data Obat' : 'Tambah Obat Baru' }}
                </h2>
                <button
                    type="button"
                    @click="closeModal"
                    class="rounded p-1 text-slate-400 hover:text-slate-600"
                >
                    <X class="size-4" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-3 p-4 text-xs">
                <div class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Kategori Obat</label
                    >
                    <Select
                        :model-value="
                            form.medication_category_id
                                ? String(form.medication_category_id)
                                : ''
                        "
                        @update:model-value="
                            (val) => (form.medication_category_id = val)
                        "
                    >
                        <SelectTrigger
                            class="h-8 w-full border-slate-200 bg-white text-xs"
                        >
                            <SelectValue placeholder="Pilih Kategori Obat" />
                        </SelectTrigger>
                        <SelectContent class="border-slate-200 bg-white">
                            <SelectItem
                                v-for="category in medication_categories"
                                :key="
                                    category.medication_category_id ??
                                    category.id
                                "
                                :value="
                                    String(
                                        category.medication_category_id ??
                                            category.id,
                                    )
                                "
                                class="text-xs"
                            >
                                {{ category.medication_category_name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Nama Obat</label
                    >
                    <input
                        type="text"
                        v-model="form.medication_name"
                        placeholder="Contoh: Paracetamol 500mg"
                        class="h-8 w-full rounded-md border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                    />
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700"
                            >Stok Saat Ini</label
                        >
                        <input
                            type="number"
                            v-model="form.stock"
                            min="0"
                            placeholder="0"
                            class="h-8 w-full rounded-md border border-slate-200 px-3 py-1 font-mono text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700"
                            >Batas Min. Stok</label
                        >
                        <input
                            type="number"
                            v-model="form.min_stock"
                            min="0"
                            placeholder="10"
                            class="h-8 w-full rounded-md border border-slate-200 px-3 py-1 font-mono text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700"
                            >Harga Satuan</label
                        >
                        <input
                            type="number"
                            v-model="form.unit_price"
                            min="0"
                            placeholder="0"
                            class="h-8 w-full rounded-md border border-slate-200 px-3 py-1 font-mono text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div
                    class="flex items-center justify-end gap-2 border-t border-slate-100 pt-2"
                >
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="closeModal"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        size="sm"
                        :disabled="form.processing"
                        class="bg-indigo-600 text-white hover:bg-indigo-700"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </form>
        </MotionModal>

        <!-- Dialog Alert Konfirmasi Hapus Obat (Spring Modal) -->
        <ConfirmDeleteModal
            :open="isDeleteModalOpen"
            @update:open="isDeleteModalOpen = $event"
            title="Hapus Data Obat"
            description="Konfirmasi penghapusan data obat dari inventaris farmasi."
            :processing="isDeleting"
            @confirm="handleDeleteMedication"
            @cancel="isDeleteModalOpen = false"
        >
            Apakah Anda yakin ingin menghapus obat
            <span class="font-semibold text-slate-900"
                >"{{ medicationToDelete?.medication_name }}"</span
            >? Data riwayat transaksi obat ini dapat terpengaruh.
        </ConfirmDeleteModal>
    </div>
</template>
