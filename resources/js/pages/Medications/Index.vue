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
} from 'lucide-vue-next';

const props = defineProps({
    medications: Object,
    errors: Object,
    medication_categories: Array,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMedicationId = ref(null);

const form = useForm({
    medication_category_id: '',
    medication_name: '',
    stock: '',
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

const deleteMedication = (medication) => {
    const id = medication.medication_id ?? medication.id;
    if (confirm(`Hapus data obat "${medication.medication_name}"?`)) {
        router.delete(medicationRoute.destroy.url(id));
    }
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

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Data Obat & Farmasi</h1>
                <p class="text-xs text-slate-500">Kelola inventaris obat-obatan, stok persediaan, dan harga satuan klinik.</p>
            </div>
            <Button size="sm" class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white" @click="openAddModal">
                <Plus class="size-3.5" />
                Tambah Obat
            </Button>
        </div>

        <!-- Clean Standard Table (Tanpa Badge AI) -->
        <Card class="p-0 overflow-hidden border border-slate-200/80 bg-white shadow-xs rounded-lg">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12 text-center">No</TableHead>
                        <TableHead>Nama Obat</TableHead>
                        <TableHead class="w-48">Kategori</TableHead>
                        <TableHead class="w-32 text-right">Stok</TableHead>
                        <TableHead class="w-40 text-right">Harga Satuan</TableHead>
                        <TableHead class="text-right pr-4 w-28">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(item, index) in medications.data"
                        :key="item.medication_id ?? item.id"
                    >
                        <TableCell class="text-center text-slate-400 font-mono text-[11px]">
                            {{ (medications.current_page - 1) * medications.per_page + (index + 1) }}
                        </TableCell>
                        <TableCell class="font-medium text-xs text-slate-900">
                            {{ item.medication_name }}
                        </TableCell>
                        <TableCell class="text-xs text-slate-600">
                            {{ item.medication_category?.medication_category_name || '-' }}
                        </TableCell>
                        <TableCell class="text-right text-xs font-mono font-medium" :class="item.stock <= 5 ? 'text-rose-600' : 'text-slate-800'">
                            {{ item.stock }} Unit
                        </TableCell>
                        <TableCell class="text-right text-xs font-mono text-slate-700">
                            {{ formatCurrency(item.unit_price) }}
                        </TableCell>
                        <TableCell class="text-right pr-4">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 text-slate-500 hover:text-indigo-600 border-slate-200"
                                    @click="openEditModal(item)"
                                    title="Edit"
                                >
                                    <Pencil class="size-3" />
                                </Button>
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 text-slate-400 hover:text-rose-600 hover:border-rose-200 border-slate-200"
                                    @click="deleteMedication(item)"
                                    title="Hapus"
                                >
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="medications.data.length === 0" :colspan="6">
                        <div class="py-10 text-center text-slate-400 text-xs">
                            Belum ada data obat terdaftar.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Pagination Bar -->
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-500">
                <span>
                    Hal <strong class="text-slate-700">{{ medications.current_page }}</strong> dari {{ medications.last_page }}
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!medications.prev_page_url"
                        as-child
                    >
                        <Link v-if="medications.prev_page_url" :href="medications.prev_page_url">
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
                        :disabled="!medications.next_page_url"
                        as-child
                    >
                        <Link v-if="medications.next_page_url" :href="medications.next_page_url">
                            Next <ChevronRight class="size-3" />
                        </Link>
                        <span v-else class="opacity-40 flex items-center">
                            Next <ChevronRight class="size-3" />
                        </span>
                    </Button>
                </div>
            </div>
        </Card>

        <!-- Modal Form Tambah / Edit -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 backdrop-blur-2xs p-4">
            <div class="w-full max-w-md bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                    <h2 class="text-sm font-bold text-slate-900">
                        {{ isEditMode ? 'Edit Data Obat' : 'Tambah Obat Baru' }}
                    </h2>
                    <button type="button" @click="closeModal" class="text-slate-400 hover:text-slate-600 rounded p-1">
                        <X class="size-4" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-4 space-y-3 text-xs">
                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Kategori Obat</label>
                        <select
                            v-model="form.medication_category_id"
                            class="w-full h-8 rounded-md border border-slate-200 px-3 py-1.5 text-xs text-slate-800 bg-white focus:border-indigo-500 focus:outline-none"
                        >
                            <option value="">Pilih Kategori</option>
                            <option
                                v-for="category in medication_categories"
                                :key="category.medication_category_id ?? category.id"
                                :value="category.medication_category_id ?? category.id"
                            >
                                {{ category.medication_category_name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Nama Obat</label>
                        <input
                            type="text"
                            v-model="form.medication_name"
                            placeholder="Contoh: Paracetamol 500mg"
                            class="w-full h-8 rounded-md border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Stok</label>
                            <input
                                type="number"
                                v-model="form.stock"
                                min="0"
                                placeholder="0"
                                class="w-full h-8 rounded-md border border-slate-200 px-3 py-1 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none font-mono"
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Harga Satuan (Rp)</label>
                            <input
                                type="number"
                                v-model="form.unit_price"
                                min="0"
                                placeholder="0"
                                class="w-full h-8 rounded-md border border-slate-200 px-3 py-1 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none font-mono"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <Button type="button" variant="outline" size="sm" @click="closeModal">Batal</Button>
                        <Button type="submit" size="sm" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
