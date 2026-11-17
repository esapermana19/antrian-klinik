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
    Plus,
    Pencil,
    Trash2,
    Search,
    ChevronLeft,
    ChevronRight,
    X,
    Stethoscope,
    Phone,
    Upload,
    User,
} from 'lucide-vue-next';
import MotionModal from '@/components/MotionModal.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';

const props = defineProps({
    doctors: Object,
    filters: Object,
    errors: Object,
});

const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/doctor',
            { search: searchQuery.value || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
};

// Modal Form Tambah/Edit
const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedDoctorId = ref(null);
const previewPhotoUrl = ref(null);

const form = useForm({
    doctor_name: '',
    specialization: '',
    queue_prefix: '',
    str_number: '',
    phone_number: '',
    is_active: true,
    photo: null,
});

// Modal Delete
const isDeleteModalOpen = ref(false);
const doctorToDelete = ref(null);
const isDeleting = ref(false);

const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    form.photo = null;
    previewPhotoUrl.value = null;
    isModalOpen.value = true;
};

const openEditModal = (doctor) => {
    isEditMode.value = true;
    selectedDoctorId.value = doctor.doctor_id;
    form.clearErrors();
    form.doctor_name = doctor.doctor_name;
    form.specialization = doctor.specialization;
    form.queue_prefix = doctor.queue_prefix || '';
    form.str_number = doctor.str_number || '';
    form.phone_number = doctor.phone_number || '';
    form.is_active = Boolean(doctor.is_active);
    form.photo = null;
    previewPhotoUrl.value =
        doctor.photo_url ||
        (doctor.photo_path ? `/storage/${doctor.photo_path}` : null);
    isModalOpen.value = true;
};

const handlePhotoChange = (e) => {
    const file = e.target.files?.[0];
    if (file) {
        form.photo = file;
        previewPhotoUrl.value = URL.createObjectURL(file);
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    previewPhotoUrl.value = null;
};

const submitForm = () => {
    if (isEditMode.value && selectedDoctorId.value) {
        form.post(`/doctor/${selectedDoctorId.value}`, {
            headers: {
                'X-HTTP-Method-Override': 'PUT',
            },
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/doctor', {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDeleteDoctor = (doctor) => {
    doctorToDelete.value = doctor;
    isDeleteModalOpen.value = true;
};

const handleDeleteDoctor = () => {
    if (!doctorToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/doctor/${doctorToDelete.value.doctor_id}`, {
        onFinish: () => {
            isDeleting.value = false;
            isDeleteModalOpen.value = false;
            doctorToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Master Data Dokter" />

    <div class="max-w-7xl space-y-4 p-4 md:p-6">
        <!-- Top Action / Header Area -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">
                    Master Data Dokter
                </h1>
                <p class="text-xs text-slate-500">
                    Kelola informasi tenaga medis, spesialisasi, dan status
                    praktik dokter.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Search Input Dokter -->
                <div class="relative w-64">
                    <Search
                        class="absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="handleSearch"
                        placeholder="Cari nama atau spesialis..."
                        class="h-8 w-full rounded-lg border border-slate-200/80 bg-white/80 pr-3 pl-8 text-xs shadow-2xs transition focus:border-indigo-500 focus:bg-white focus:outline-none"
                    />
                </div>

                <Button
                    size="sm"
                    class="gap-1.5 bg-indigo-600 font-medium text-white shadow-xs hover:bg-indigo-700"
                    @click="openAddModal"
                >
                    <Plus class="size-3.5" />
                    Tambah Dokter
                </Button>
            </div>
        </div>

        <!-- Main Card & Clean Table (Shadcn Style) -->
        <Card
            class="overflow-hidden rounded-xl border border-slate-200/80 bg-white p-0 shadow-xs"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12 text-center">No</TableHead>
                        <TableHead class="w-56">Dokter & Spesialis</TableHead>
                        <TableHead class="w-44">No. STR</TableHead>
                        <TableHead class="w-36">Kontak Telepon</TableHead>
                        <TableHead class="w-32 text-center">Status</TableHead>
                        <TableHead class="w-36">Jadwal / Antrean</TableHead>
                        <TableHead class="w-28 pr-4 text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(doc, index) in doctors.data"
                        :key="doc.doctor_id"
                    >
                        <!-- No -->
                        <TableCell
                            class="text-center font-mono text-[11px] text-slate-400"
                        >
                            {{
                                (doctors.current_page - 1) * doctors.per_page +
                                (index + 1)
                            }}
                        </TableCell>

                        <!-- Nama Dokter & Spesialis -->
                        <TableCell>
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200/80 bg-slate-100"
                                >
                                    <img
                                        v-if="doc.photo_url || doc.photo_path"
                                        :src="
                                            doc.photo_url ||
                                            `/storage/${doc.photo_path}`
                                        "
                                        :alt="doc.doctor_name"
                                        class="size-full object-cover"
                                    />
                                    <User
                                        v-else
                                        class="size-4 text-slate-400"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="text-xs font-medium text-slate-900"
                                            >{{ doc.doctor_name }}</span
                                        >
                                        <span
                                            v-if="doc.queue_prefix"
                                            class="inline-flex items-center rounded border border-indigo-200/60 bg-indigo-50 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-indigo-700"
                                            title="Prefix nomor antrean dokter"
                                        >
                                            {{ doc.queue_prefix }}
                                        </span>
                                    </div>
                                    <div
                                        class="mt-0.5 flex items-center gap-1 text-[11px] text-slate-500"
                                    >
                                        <Stethoscope
                                            class="size-3 text-indigo-500"
                                        />
                                        {{ doc.specialization }}
                                    </div>
                                </div>
                            </div>
                        </TableCell>

                        <!-- No. STR -->
                        <TableCell class="font-mono text-xs text-slate-700">
                            {{ doc.str_number || '-' }}
                        </TableCell>

                        <!-- Kontak Telepon -->
                        <TableCell class="font-mono text-xs text-slate-600">
                            {{ doc.phone_number || '-' }}
                        </TableCell>

                        <!-- Status Praktik (Clean Dot Indicator Konsisten) -->
                        <TableCell class="text-center">
                            <span
                                v-if="doc.is_active"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-emerald-600"
                                ></span>
                                Aktif
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-slate-400"
                                ></span>
                                Nonaktif
                            </span>
                        </TableCell>

                        <!-- Jadwal & Antrean -->
                        <TableCell class="font-mono text-xs text-slate-500">
                            {{ doc.practice_schedules_count ?? 0 }} jadwal •
                            {{ doc.queues_count ?? 0 }} antrean
                        </TableCell>

                        <!-- Aksi -->
                        <TableCell class="pr-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 border-slate-200 text-slate-500 hover:text-indigo-600"
                                    @click="openEditModal(doc)"
                                    title="Edit"
                                >
                                    <Pencil class="size-3" />
                                </Button>
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 border-slate-200 text-slate-400 hover:border-rose-200 hover:text-rose-600"
                                    @click="confirmDeleteDoctor(doc)"
                                    title="Hapus"
                                >
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="doctors.data.length === 0" :colspan="7">
                        <div class="py-10 text-center text-xs text-slate-400">
                            Belum ada data dokter terdaftar.
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
                        doctors.current_page
                    }}</strong>
                    dari {{ doctors.last_page }} (Total: {{ doctors.total }})
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!doctors.prev_page_url"
                        as-child
                    >
                        <Link
                            v-if="doctors.prev_page_url"
                            :href="doctors.prev_page_url"
                        >
                            <ChevronLeft class="size-3" />
                            Prev
                        </Link>
                        <span v-else class="flex items-center opacity-40">
                            <ChevronLeft class="size-3" />
                            Prev
                        </span>
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!doctors.next_page_url"
                        as-child
                    >
                        <Link
                            v-if="doctors.next_page_url"
                            :href="doctors.next_page_url"
                        >
                            Next
                            <ChevronRight class="size-3" />
                        </Link>
                        <span v-else class="flex items-center opacity-40">
                            Next
                            <ChevronRight class="size-3" />
                        </span>
                    </Button>
                </div>
            </div>
        </Card>

        <!-- Modal Form Tambah / Edit (MotionModal Style Konsisten) -->
        <MotionModal
            :show="isModalOpen"
            max-width="max-w-md"
            @close="closeModal"
        >
            <div
                class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3"
            >
                <h2 class="text-sm font-bold text-slate-900">
                    {{ isEditMode ? 'Edit Data Dokter' : 'Tambah Dokter Baru' }}
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
                <!-- Foto Profil Dokter -->
                <div class="space-y-1.5">
                    <label class="font-semibold text-slate-700"
                        >Foto Profil Dokter</label
                    >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-100"
                        >
                            <img
                                v-if="previewPhotoUrl"
                                :src="previewPhotoUrl"
                                alt="Preview"
                                class="size-full object-cover"
                            />
                            <User v-else class="size-6 text-slate-400" />
                        </div>
                        <div class="space-y-1">
                            <label
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-2xs transition hover:bg-slate-50"
                            >
                                <Upload class="size-3.5 text-slate-500" />
                                <span>Pilih Foto</span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handlePhotoChange"
                                />
                            </label>
                            <p class="text-[10px] text-slate-400">
                                Format: JPG, PNG, WEBP (Maksimal 2MB).
                            </p>
                        </div>
                    </div>
                    <p
                        v-if="form.errors.photo"
                        class="mt-0.5 text-[11px] text-rose-500"
                    >
                        {{ form.errors.photo }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Nama Dokter Lengkap
                        <span class="text-rose-500">*</span></label
                    >
                    <input
                        type="text"
                        v-model="form.doctor_name"
                        placeholder="Contoh: dr. Budi Santoso, Sp.A"
                        class="h-8 w-full rounded-lg border border-slate-200 px-2.5 text-xs focus:border-indigo-500 focus:outline-none"
                        required
                    />
                    <p
                        v-if="form.errors.doctor_name"
                        class="mt-0.5 text-[11px] text-rose-500"
                    >
                        {{ form.errors.doctor_name }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Spesialisasi / Poli
                        <span class="text-rose-500">*</span></label
                    >
                    <input
                        type="text"
                        v-model="form.specialization"
                        placeholder="Contoh: Dokter Umum / Spesialis Anak / Gigi"
                        class="h-8 w-full rounded-lg border border-slate-200 px-2.5 text-xs focus:border-indigo-500 focus:outline-none"
                        required
                    />
                    <p
                        v-if="form.errors.specialization"
                        class="mt-0.5 text-[11px] text-rose-500"
                    >
                        {{ form.errors.specialization }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Prefix Nomor Antrean (Opsional)</label
                    >
                    <input
                        type="text"
                        v-model="form.queue_prefix"
                        placeholder="Contoh: GGI, ANK, UMM (Jika kosong, memakai default klinik)"
                        maxlength="10"
                        class="h-8 w-full rounded-lg border border-slate-200 px-2.5 font-mono text-xs uppercase focus:border-indigo-500 focus:outline-none"
                    />
                    <p class="text-[10px] text-slate-400">
                        Contoh hasil: <strong>GGI-001</strong> untuk membedakan
                        antrean poli/dokter di display TV.
                    </p>
                    <p
                        v-if="form.errors.queue_prefix"
                        class="mt-0.5 text-[11px] text-rose-500"
                    >
                        {{ form.errors.queue_prefix }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700"
                            >Nomor STR</label
                        >
                        <input
                            type="text"
                            v-model="form.str_number"
                            placeholder="Nomor Surat Tanda Registrasi"
                            class="h-8 w-full rounded-lg border border-slate-200 px-2.5 font-mono text-xs focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700"
                            >No. Telepon / WA</label
                        >
                        <input
                            type="text"
                            v-model="form.phone_number"
                            placeholder="08xxxxxxxxxx"
                            class="h-8 w-full rounded-lg border border-slate-200 px-2.5 font-mono text-xs focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input
                        v-model="form.is_active"
                        id="doctor_is_active"
                        type="checkbox"
                        class="cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <label
                        for="doctor_is_active"
                        class="cursor-pointer text-xs font-medium text-slate-700 select-none"
                    >
                        Dokter Aktif Melayani Praktik
                    </label>
                </div>

                <div
                    class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3"
                >
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="h-8 border-slate-200 text-xs"
                        @click="closeModal"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        size="sm"
                        class="h-8 bg-indigo-600 text-xs font-medium text-white hover:bg-indigo-700"
                        :disabled="form.processing"
                    >
                        {{ isEditMode ? 'Simpan Perubahan' : 'Tambah Dokter' }}
                    </Button>
                </div>
            </form>
        </MotionModal>

        <!-- Modal Konfirmasi Hapus -->
        <ConfirmDeleteModal
            :show="isDeleteModalOpen"
            title="Hapus Dokter?"
            :message="`Apakah Anda yakin ingin menghapus data ${doctorToDelete?.doctor_name}? Jadwal dan antrean terkait akan terlepas.`"
            @close="isDeleteModalOpen = false"
            @confirm="handleDeleteDoctor"
        />
    </div>
</template>
