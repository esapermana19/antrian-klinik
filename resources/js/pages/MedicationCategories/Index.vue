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
    Search,
} from 'lucide-vue-next';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import MotionModal from '@/components/MotionModal.vue';

const props = defineProps({
    medication_categories: Object,
    errors: Object,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/medication-category',
            { search: searchQuery.value || undefined },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300);
};

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMedicationCategoryId = ref(null);

// State untuk Dialog Alert Konfirmasi Hapus Data
const isDeleteModalOpen = ref(false);
const categoryToDelete = ref(null);
const isDeleting = ref(false);

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

const confirmDeleteCategory = (dataCategory) => {
    categoryToDelete.value = dataCategory;
    isDeleteModalOpen.value = true;
};

const handleDeleteCategory = () => {
    if (!categoryToDelete.value) return;
    const id = categoryToDelete.value.medication_category_id ?? categoryToDelete.value.id;
    isDeleting.value = true;
    router.delete(medicationCategoryRoute.destroy.url(id), {
        onFinish: () => {
            isDeleting.value = false;
            isDeleteModalOpen.value = false;
            categoryToDelete.value = null;
        },
    });
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
            <div class="flex items-center gap-2">
                <!-- Search Input Kategori Obat -->
                <div class="relative w-64">
                    <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 size-3.5 text-slate-400" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="handleSearch"
                        placeholder="Cari nama atau kode kategori..."
                        class="w-full h-8 pl-8 pr-3 text-xs rounded-lg border border-slate-200/80 bg-white/80 focus:bg-white focus:border-indigo-500 focus:outline-none transition shadow-2xs"
                    />
                </div>
                <Button size="sm" class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs" @click="openAddModal">
                    <Plus class="size-3.5" />
                    Tambah Kategori
                </Button>
            </div>
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
                                    @click="confirmDeleteCategory(item)"
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

        <!-- Modal Form (Physics Spring Motion) -->
        <MotionModal :show="isModalOpen" max-width="max-w-md" @close="closeModal">
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
        </MotionModal>

        <!-- Dialog Alert Konfirmasi Hapus Data (Spring Modal) -->
        <ConfirmDeleteModal
            :open="isDeleteModalOpen"
            @update:open="isDeleteModalOpen = $event"
            title="Hapus Kategori Obat"
            description="Konfirmasi penghapusan data kategori obat terpilih."
            :processing="isDeleting"
            @confirm="handleDeleteCategory"
            @cancel="isDeleteModalOpen = false"
        >
            Apakah Anda yakin ingin menghapus kategori obat
            <span class="font-semibold text-slate-900">"{{ categoryToDelete?.medication_category_name }}"</span>?
            Obat yang terdaftar dalam kategori ini dapat terpengaruh.
        </ConfirmDeleteModal>
    </div>
</template>
