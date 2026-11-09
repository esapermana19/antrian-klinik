<script setup>
import { ref } from 'vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
import patientRoute from '@/routes/patient';
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
    FileText,
    Pencil,
    Trash2,
    ChevronLeft,
    ChevronRight,
    X,
    HeartPulse,
    Thermometer,
    Weight,
    Stethoscope,
    Pill,
    Search,
    ChevronDown,
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
    patients: Object,
    errors: Object,
    users: Array,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/patient',
            { search: searchQuery.value || undefined },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300);
};

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedPatientId = ref(null);

// State untuk Dialog Alert Konfirmasi Hapus Pasien
const isDeleteModalOpen = ref(false);
const patientToDelete = ref(null);
const isDeleting = ref(false);

// State untuk Modal Riwayat Rekam Medis
const isHistoryOpen = ref(false);
const selectedPatient = ref(null);
const medicalRecords = ref([]);
const isLoadingHistory = ref(false);
const expandedRecords = ref({}); // Track id rekam medis yang sedang dibuka

const toggleAccordion = (id) => {
    expandedRecords.value[id] = !expandedRecords.value[id];
};

const form = useForm({
    user_id: '',
    nik: '',
    patient_name: '',
    date_of_birth: '',
    gender: '',
    address: '',
});

const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (patient) => {
    isEditMode.value = true;
    selectedPatientId.value = patient.patient_id ?? patient.id;
    form.clearErrors();
    form.user_id = patient.user_id ?? patient.user_id?.patient_id ?? '';
    form.nik = patient.nik;
    form.patient_name = patient.patient_name;
    form.date_of_birth = patient.date_of_birth;
    form.gender = patient.gender;
    form.address = patient.address;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(patientRoute.update.url(selectedPatientId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(patientRoute.store.url(), {
            onSuccess: () => closeModal(),
        });
    }
};

const openHistoryModal = async (patient) => {
    selectedPatient.value = patient;
    medicalRecords.value = [];
    expandedRecords.value = {};
    isLoadingHistory.value = true;
    isHistoryOpen.value = true;

    try {
        const id = patient.patient_id ?? patient.id;
        const response = await fetch(`/patients/${id}/medical-records`);
        if (!response.ok) {
            throw new Error('Gagal mengambil data rekam medis.');
        }
        const data = await response.json();
        medicalRecords.value = data;
    } catch (error) {
        console.error('Error fetching medical records:', error);
        alert('Gagal mengambil riwayat rekam medis.');
    } finally {
        isLoadingHistory.value = false;
    }
};

const closeHistoryModal = () => {
    isHistoryOpen.value = false;
    selectedPatient.value = null;
    medicalRecords.value = [];
};

const confirmDeletePatient = (patient) => {
    patientToDelete.value = patient;
    isDeleteModalOpen.value = true;
};

const handleDeletePatient = () => {
    if (!patientToDelete.value) return;
    const id = patientToDelete.value.patient_id ?? patientToDelete.value.id;
    isDeleting.value = true;
    router.delete(patientRoute.destroy.url(id), {
        onFinish: () => {
            isDeleting.value = false;
            isDeleteModalOpen.value = false;
            patientToDelete.value = null;
        },
    });
};

const formatGender = (gender) => {
    const genderName = {
        male: 'Laki-laki',
        female: 'Perempuan',
    };
    return genderName[gender] || gender;
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
    <Head title="Master Data Pasien" />

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Top Action / Header Area -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Master Data Pasien</h1>
                <p class="text-xs text-slate-500">Kelola dan pantau seluruh data pasien klinik dengan rekam medis terpadu.</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Search Input Pasien by NIK / Nama -->
                <div class="relative w-64">
                    <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 size-3.5 text-slate-400" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="handleSearch"
                        placeholder="Cari nama atau NIK..."
                        class="w-full h-8 pl-8 pr-3 text-xs rounded-lg border border-slate-200/80 bg-white/80 focus:bg-white focus:border-indigo-500 focus:outline-none transition shadow-2xs"
                    />
                </div>
                <Button size="sm" class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-xs" @click="openAddModal">
                    <Plus class="size-3.5" />
                    Tambah Pasien
                </Button>
            </div>
        </div>

        <!-- Main Card & Clean Table (Shadcn Style) -->
        <Card class="p-0 overflow-hidden border border-slate-200/80 bg-white shadow-xs rounded-xl">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12 text-center">No</TableHead>
                        <TableHead class="w-40">NIK</TableHead>
                        <TableHead>Nama Pasien</TableHead>
                        <TableHead>Tgl Lahir</TableHead>
                        <TableHead>Gender</TableHead>
                        <TableHead>Alamat</TableHead>
                        <TableHead class="text-right pr-4">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(item, index) in patients.data"
                        :key="item.patient_id ?? item.id"
                    >
                        <!-- No -->
                        <TableCell class="text-center text-slate-400 font-mono text-[11px]">
                            {{ (patients.current_page - 1) * patients.per_page + (index + 1) }}
                        </TableCell>

                        <!-- NIK -->
                        <TableCell class="font-mono text-xs text-slate-700">
                            {{ item.nik }}
                        </TableCell>

                        <!-- Nama Pasien -->
                        <TableCell class="font-medium text-xs text-slate-900">
                            {{ item.patient_name }}
                        </TableCell>

                        <!-- Tgl Lahir -->
                        <TableCell class="text-xs text-slate-600 font-mono">
                            {{ item.date_of_birth || '-' }}
                        </TableCell>

                        <!-- Gender -->
                        <TableCell class="text-xs text-slate-600">
                            {{ formatGender(item.gender) }}
                        </TableCell>

                        <!-- Alamat -->
                        <TableCell class="text-xs text-slate-500 max-w-xs truncate">
                            {{ item.address || '-' }}
                        </TableCell>

                        <!-- Aksi -->
                        <TableCell class="text-right pr-4">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="h-7 px-2 text-xs font-normal text-indigo-700 hover:text-indigo-800 hover:bg-indigo-50 border-slate-200"
                                    @click="openHistoryModal(item)"
                                >
                                    <FileText class="size-3 mr-1" />
                                    Riwayat
                                </Button>
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
                                    @click="confirmDeletePatient(item)"
                                    title="Hapus"
                                >
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="patients.data.length === 0" :colspan="7">
                        <div class="py-10 text-center text-slate-400 text-xs">
                            Belum ada data pasien terdaftar.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Pagination Bar -->
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-500">
                <span>
                    Hal <strong class="text-slate-700">{{ patients.current_page }}</strong> dari {{ patients.last_page }} (Total: {{ patients.total }})
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!patients.prev_page_url"
                        as-child
                    >
                        <Link v-if="patients.prev_page_url" :href="patients.prev_page_url">
                            <ChevronLeft class="size-3" />
                            Prev
                        </Link>
                        <span v-else class="opacity-40 flex items-center">
                            <ChevronLeft class="size-3" />
                            Prev
                        </span>
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!patients.next_page_url"
                        as-child
                    >
                        <Link v-if="patients.next_page_url" :href="patients.next_page_url">
                            Next
                            <ChevronRight class="size-3" />
                        </Link>
                        <span v-else class="opacity-40 flex items-center">
                            Next
                            <ChevronRight class="size-3" />
                        </span>
                    </Button>
                </div>
            </div>
        </Card>

        <!-- Modal Tambah / Edit Pasien (Spring Physics Motion) -->
        <MotionModal :show="isModalOpen" max-width="max-w-md" @close="closeModal">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                <h2 class="text-sm font-bold text-slate-900">
                    {{ isEditMode ? 'Edit Data Pasien' : 'Tambah Pasien Baru' }}
                </h2>
                <button type="button" @click="closeModal" class="text-slate-400 hover:text-slate-600 rounded p-1">
                    <X class="size-4" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="p-4 space-y-3 text-xs">
                <div class="space-y-1">
                    <label class="font-semibold text-slate-700">NIK (16 Digit)</label>
                    <input
                        type="text"
                        v-model="form.nik"
                        placeholder="320..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                    />
                    <span v-if="form.errors.nik" class="text-[10px] text-rose-500">{{ form.errors.nik }}</span>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700">Nama Lengkap Pasien</label>
                    <input
                        type="text"
                        v-model="form.patient_name"
                        placeholder="Nama pasien..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                    />
                    <span v-if="form.errors.patient_name" class="text-[10px] text-rose-500">{{ form.errors.patient_name }}</span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Tanggal Lahir</label>
                        <input
                            type="date"
                            v-model="form.date_of_birth"
                            class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Jenis Kelamin</label>
                        <Select :model-value="form.gender" @update:model-value="(val) => form.gender = val">
                            <SelectTrigger class="w-full h-8 text-xs bg-white border-slate-200">
                                <SelectValue placeholder="Pilih Jenis Kelamin" />
                            </SelectTrigger>
                            <SelectContent class="bg-white border-slate-200">
                                <SelectItem value="male" class="text-xs">Laki-laki</SelectItem>
                                <SelectItem value="female" class="text-xs">Perempuan</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700">Alamat Rumah</label>
                    <textarea
                        v-model="form.address"
                        rows="2"
                        placeholder="Alamat domisili..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <Button type="button" variant="outline" size="sm" @click="closeModal">Batal</Button>
                    <Button type="submit" size="sm" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </form>
        </MotionModal>

        <!-- Modal Riwayat Rekam Medis (Spring Physics Motion) -->
        <MotionModal :show="isHistoryOpen" max-width="max-w-xl" @close="closeHistoryModal">
            <div class="max-h-[85vh] flex flex-col overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Riwayat Rekam Medis</h2>
                        <p class="text-[11px] text-slate-500">
                            {{ selectedPatient?.patient_name }} (NIK: {{ selectedPatient?.nik }})
                        </p>
                    </div>
                    <button type="button" @click="closeHistoryModal" class="text-slate-400 hover:text-slate-600 rounded p-1">
                        <X class="size-4" />
                    </button>
                </div>

                <div class="p-4 overflow-y-auto space-y-3 text-xs">
                    <div v-if="isLoadingHistory" class="text-center py-8 text-slate-400">
                        Memuat riwayat medis...
                    </div>
                    <div v-else-if="medicalRecords.length === 0" class="text-center py-8 text-slate-400">
                        Belum ada riwayat rekam medis.
                    </div>
                    <div v-else class="space-y-2">
                        <!-- Accordion Style List -->
                        <div
                            v-for="record in medicalRecords"
                            :key="record.medrec_id"
                            class="rounded-lg border border-slate-200 bg-white overflow-hidden shadow-2xs transition"
                        >
                            <!-- Accordion Header (Baris Ringkas: Kode, Tanggal, Diagnosa Singkat) -->
                            <button
                                type="button"
                                @click="toggleAccordion(record.medrec_id)"
                                class="w-full px-3 py-2.5 flex items-center justify-between text-left hover:bg-slate-50 transition cursor-pointer"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="size-6 rounded-md flex items-center justify-center transition-transform text-slate-400"
                                        :class="expandedRecords[record.medrec_id] ? 'rotate-180 text-indigo-600' : ''"
                                    >
                                        <ChevronDown class="size-4" />
                                    </div>
                                    <div>
                                        <span class="font-mono font-bold text-xs text-slate-800">
                                            {{ record.medrec_code }}
                                        </span>
                                        <span class="ml-2 text-[11px] text-slate-500 font-medium">
                                            Diagnosa: <strong class="text-slate-800">{{ record.diagnosis || '-' }}</strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="text-[11px] text-slate-400 font-mono">
                                    {{ formatDate(record.created_at) }}
                                </div>
                            </button>

                            <!-- Accordion Detail (Hanya tampil saat dibuka) -->
                            <div
                                v-show="expandedRecords[record.medrec_id]"
                                class="px-3 pb-3 pt-2 border-t border-slate-100 bg-slate-50/60 space-y-2.5 text-xs text-slate-700"
                            >
                                <div class="grid grid-cols-3 gap-2 text-[11px] p-2 bg-white rounded-md border border-slate-100">
                                    <div>Tensi: <strong class="text-slate-900 font-mono">{{ record.blood_pressure || '-' }}</strong></div>
                                    <div>Suhu: <strong class="text-slate-900 font-mono">{{ record.temperature || '-' }}°C</strong></div>
                                    <div>Berat: <strong class="text-slate-900 font-mono">{{ record.weight || '-' }}kg</strong></div>
                                </div>

                                <div class="space-y-1">
                                    <div><span class="text-slate-500">Keluhan:</span> {{ record.main_complaint || '-' }}</div>
                                    <div v-if="record.notes_on_treatment"><span class="text-slate-500">Tindakan:</span> {{ record.notes_on_treatment }}</div>
                                </div>

                                <div v-if="record.prescriptions && record.prescriptions.length > 0" class="pt-2 border-t border-slate-200/80 text-[11px]">
                                    <span class="text-slate-500 font-medium">Resep Obat: </span>
                                    <span
                                        v-for="rx in record.prescriptions"
                                        :key="rx.prescription_id"
                                        class="inline-block mr-2 text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200"
                                    >
                                        {{ rx.medication?.medication_name }} ({{ rx.qty }} unit, {{ rx.dosage }})
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-2 border-t border-slate-100 bg-slate-50/80 flex justify-end">
                    <Button variant="outline" size="sm" @click="closeHistoryModal">Tutup</Button>
                </div>
            </div>
        </MotionModal>

        <!-- Dialog Alert Konfirmasi Hapus Pasien (Spring Modal) -->
        <ConfirmDeleteModal
            :open="isDeleteModalOpen"
            @update:open="isDeleteModalOpen = $event"
            title="Hapus Data Pasien"
            description="Konfirmasi penghapusan data pasien dari master database."
            :processing="isDeleting"
            @confirm="handleDeletePatient"
            @cancel="isDeleteModalOpen = false"
        >
            Apakah Anda yakin ingin menghapus data pasien
            <span class="font-semibold text-slate-900">"{{ patientToDelete?.patient_name }}"</span>
            (NIK: <span class="font-mono text-slate-800">{{ patientToDelete?.nik }}</span>)?
            Seluruh catatan antrean yang terkait akan ikut dibatalkan.
        </ConfirmDeleteModal>
    </div>
</template>
