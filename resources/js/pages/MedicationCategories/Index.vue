<script setup>
import { ref } from 'vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
import medicationCategoryRoute from '@/routes/medication-category';
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
    medication_categories: Object,
    errors: Object,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMedicationCategoryId = ref(null);

const form = useForm({
    medication_category_code: '',
    medication_category_name: '',
});

const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (dataCategory) => {
    isEditMode.value = true;
    selectedMedicationCategoryId.value =
        dataCategory.medication_category_id ?? dataCategory.id;
    form.clearErrors();
    form.medication_category_code = dataCategory.medication_category_code;
    form.medication_category_name = dataCategory.medication_category_name;

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(
            medicationCategoryRoute.update.url(
                selectedMedicationCategoryId.value,
            ),
            {
                onSuccess: () => closeModal(),
            },
        );
    } else {
        form.post(medicationCategoryRoute.store.url(), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteMedicationCategory = (dataCategory) => {
    const id = dataCategory.medication_category_id ?? dataCategory.id;
    if (confirm(`Hapus kategori obat "${dataCategory.medication_category_name}"?`)) {
        router.delete(medicationCategoryRoute.destroy.url(id));
    }
};
</script>

<template>
    <Head title="Kategori Obat" />

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Master Kategori Obat</h1>
                <p class="text-xs text-slate-500">Kelola kategori obat untuk mempermudah klasifikasi data inventaris.</p>
            </div>
            <Button size="sm" class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white" @click="openAddModal">
                <Plus class="size-3.5" />
                Tambah Kategori
            </Button>
        </div>

        <!-- Clean Standard Table -->
        <Card class="p-0 overflow-hidden border border-slate-200/80 bg-white shadow-xs rounded-lg">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12 text-center">No</TableHead>
                        <TableHead class="w-36">Kode</TableHead>
                        <TableHead>Nama Kategori</TableHead>
                        <TableHead class="text-right pr-4 w-28">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(item, index) in medication_categories.data"
                        :key="item.medication_category_id ?? item.id"
                    >
                        <TableCell class="text-center text-slate-400 font-mono text-[11px]">
                            {{ (medication_categories.current_page - 1) * medication_categories.per_page + (index + 1) }}
                        </TableCell>
                        <TableCell class="font-mono text-xs font-semibold text-slate-700">
                            {{ item.medication_category_code }}
                        </TableCell>
                        <TableCell class="font-medium text-xs text-slate-900">
                            {{ item.medication_category_name }}
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
                                    @click="deleteMedicationCategory(item)"
                                    title="Hapus"
                                >
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="medication_categories.data.length === 0" :colspan="4">
                        <div class="py-10 text-center text-slate-400 text-xs">
                            Belum ada kategori obat ditambahkan.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Pagination Bar -->
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-500">
                <span>
                    Hal <strong class="text-slate-700">{{ medication_categories.current_page }}</strong> dari {{ medication_categories.last_page }}
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!medication_categories.prev_page_url"
                        as-child
                    >
                        <Link v-if="medication_categories.prev_page_url" :href="medication_categories.prev_page_url">
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
                        :disabled="!medication_categories.next_page_url"
                        as-child
                    >
                        <Link v-if="medication_categories.next_page_url" :href="medication_categories.next_page_url">
                            Next <ChevronRight class="size-3" />
                        </Link>
                        <span v-else class="opacity-40 flex items-center">
                            Next <ChevronRight class="size-3" />
                        </span>
                    </Button>
                </div>
            </div>
        </Card>

        <!-- Modal Form -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 backdrop-blur-2xs p-4">
            <div class="w-full max-w-md bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                    <h2 class="text-sm font-bold text-slate-900">
                        {{ isEditMode ? 'Edit Kategori Obat' : 'Tambah Kategori Obat' }}
                    </h2>
                    <button type="button" @click="closeModal" class="text-slate-400 hover:text-slate-600 rounded p-1">
                        <X class="size-4" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-4 space-y-3 text-xs">
                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Kode Kategori</label>
                        <input
                            type="text"
                            v-model="form.medication_category_code"
                            placeholder="Contoh: ABX, ANAL"
                            class="w-full h-8 rounded-md border border-slate-200 px-3 py-1.5 text-xs text-slate-800 uppercase focus:border-indigo-500 focus:outline-none"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Nama Kategori</label>
                        <input
                            type="text"
                            v-model="form.medication_category_name"
                            placeholder="Contoh: Antibiotik, Analgesik"
                            class="w-full h-8 rounded-md border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <Button type="button" variant="outline" size="sm" @click="closeModal">Batal</Button>
                        <Button type="submit" size="sm" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Kategori' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
