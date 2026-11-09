<script setup>
import { ref, watch, onMounted } from 'vue';
import { router, Link, useForm, Head } from '@inertiajs/vue3';
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
    PhoneCall,
    Stethoscope,
    ChevronLeft,
    ChevronRight,
    Ban,
    Plus,
    X,
    HeartPulse,
    Thermometer,
    Weight,
    Trash2,
    Clock,
    AlertCircle,
    Pill,
    CheckCircle2,
    Tv,
    FileEdit,
} from 'lucide-vue-next';
import MotionModal from '@/components/MotionModal.vue';
import SpringModal from '@/components/SpringModal.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

const props = defineProps({
    todayQueues: Object,
    medications: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const selectedDate = ref(props.filters?.date || new Date().toISOString().split('T')[0]);

const handleDateChange = () => {
    router.get(
        '/queue-management',
        { date: selectedDate.value },
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

const isModalOpen = ref(false);
const activeQueue = ref(null);

// State untuk Dialog Alert Ubah / Batalkan Status Antrean
const isStatusModalOpen = ref(false);
const statusModalData = ref(null);
const isUpdatingStatus = ref(false);

const form = useForm({
    patient_id: null,
    queue_id: null,
    main_complaint: '',
    blood_pressure: '',
    temperature: '',
    weight: '',
    diagnosis: '',
    notes_on_treatment: '',
    prescriptions: [],
});

const openMedrecModal = (queue) => {
    activeQueue.value = queue;
    form.clearErrors();
    form.patient_id = queue.patient_id ?? queue.patient?.patient_id;
    form.queue_id = queue.queue_id;

    // Cek apakah ada draft yang tersimpan di localStorage untuk antrean ini
    const draftKey = `medrec_draft_${queue.queue_id}`;
    const savedDraft = localStorage.getItem(draftKey);

    if (savedDraft) {
        try {
            const parsed = JSON.parse(savedDraft);
            form.main_complaint = parsed.main_complaint || '';
            form.blood_pressure = parsed.blood_pressure || '';
            form.temperature = parsed.temperature || '';
            form.weight = parsed.weight || '';
            form.diagnosis = parsed.diagnosis || '';
            form.notes_on_treatment = parsed.notes_on_treatment || '';
            form.prescriptions = parsed.prescriptions || [];
        } catch {
            form.reset();
        }
    } else {
        form.main_complaint = '';
        form.blood_pressure = '';
        form.temperature = '';
        form.weight = '';
        form.diagnosis = '';
        form.notes_on_treatment = '';
        form.prescriptions = [];
    }

    isModalOpen.value = true;
};

// Simpan draft otomatis ke localStorage saat dokter mengetik form
watch(
    () => [
        form.main_complaint,
        form.blood_pressure,
        form.temperature,
        form.weight,
        form.diagnosis,
        form.notes_on_treatment,
        form.prescriptions,
    ],
    () => {
        if (activeQueue.value?.queue_id && isModalOpen.value) {
            const draftKey = `medrec_draft_${activeQueue.value.queue_id}`;
            localStorage.setItem(
                draftKey,
                JSON.stringify({
                    main_complaint: form.main_complaint,
                    blood_pressure: form.blood_pressure,
                    temperature: form.temperature,
                    weight: form.weight,
                    diagnosis: form.diagnosis,
                    notes_on_treatment: form.notes_on_treatment,
                    prescriptions: form.prescriptions,
                })
            );
        }
    },
    { deep: true }
);

const closeModal = () => {
    isModalOpen.value = false;
    activeQueue.value = null;
    form.clearErrors();
    // Catatan: Tidak memanggil form.reset() di sini agar jika tidak sengaja tertutup, draft tetap aman
};

const addPrescription = () => {
    form.prescriptions.push({
        medication_id: '',
        qty: 1,
        dosage: '',
    });
};

const removePrescription = (index) => {
    form.prescriptions.splice(index, 1);
};

const submitMedicalRecord = () => {
    if (!activeQueue.value) return;

    form.post(`/medical-records/${activeQueue.value.queue_id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Bersihkan draft setelah sukses tersimpan ke database
            if (activeQueue.value?.queue_id) {
                localStorage.removeItem(`medrec_draft_${activeQueue.value.queue_id}`);
            }
            isModalOpen.value = false;
            activeQueue.value = null;
            form.reset();
        },
    });
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'called':
            return 'Dipanggil';
        case 'checking':
            return 'Diperiksa';
        case 'completed':
            return 'Selesai';
        case 'cancel':
            return 'Dibatalkan';
        default:
            return status;
    }
};

const promptUpdateStatus = (queue, newStatus) => {
    statusModalData.value = {
        queue,
        newStatus,
        title: newStatus === 'cancel' ? 'Batalkan Antrean' : 'Ubah Status Antrean',
        label: getStatusLabel(newStatus),
    };
    isStatusModalOpen.value = true;
};

const confirmUpdateStatus = () => {
    if (!statusModalData.value) return;
    const { queue, newStatus } = statusModalData.value;
    isUpdatingStatus.value = true;
    router.patch(
        `/queue-management/${queue.queue_id}/status`,
        {
            queue_status: newStatus,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isUpdatingStatus.value = false;
                isStatusModalOpen.value = false;
                statusModalData.value = null;
            },
        }
    );
};

const handleEnterExaminationRoom = (queue) => {
    router.patch(`/queue-management/${queue.queue_id}/status`, {
        queue_status: 'checking',
    }, {
        preserveScroll: true,
        onSuccess: () => {
            queue.queue_status = 'checking';
            openMedrecModal(queue);
        },
    });
};

const formatSession = (session) => {
    const sessionName = {
        morning: 'Pagi',
        afternoon: 'Sore',
    };
    return sessionName[session] || session;
};
</script>

<template>
    <Head title="Kelola Antrean Pasien" />

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Compact Clean Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Kelola Antrean Pasien</h1>
                <p class="text-xs text-slate-500">Panggilan antrean, periksa rekam medis, dan input resep obat klinik.</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Filter Tanggal Antrean -->
                <div class="flex items-center gap-1.5 bg-white/80 border border-slate-200/80 rounded-lg px-2.5 py-1 shadow-2xs">
                    <Clock class="size-3.5 text-slate-400" />
                    <label class="text-[11px] text-slate-500 font-medium">Tanggal:</label>
                    <input
                        type="date"
                        v-model="selectedDate"
                        @change="handleDateChange"
                        class="text-xs font-mono text-slate-800 bg-transparent outline-none cursor-pointer"
                    />
                </div>

                <Button variant="outline" size="sm" as-child>
                    <Link href="/display-queue" target="_blank" class="gap-1.5 text-xs text-slate-700">
                        <Tv class="size-3.5 text-indigo-600" />
                        Buka Display TV
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Table Card Shadcn Clean Design -->
        <Card class="p-0 overflow-hidden border border-slate-200/80 bg-white shadow-xs rounded-xl">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-36 whitespace-nowrap">No. Antrean</TableHead>
                        <TableHead class="min-w-[180px]">Nama Pasien</TableHead>
                        <TableHead class="w-24">Sesi</TableHead>
                        <TableHead class="w-32 whitespace-nowrap">Estimasi Jam</TableHead>
                        <TableHead class="w-32 whitespace-nowrap">Status</TableHead>
                        <TableHead class="text-right pr-4 w-44 whitespace-nowrap">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="q in todayQueues.data"
                        :key="q.queue_id"
                    >
                        <!-- No Antrean -->
                        <TableCell class="font-mono text-xs font-semibold text-slate-800 whitespace-nowrap">
                            {{ q.queue_number }}
                        </TableCell>

                        <!-- Nama Pasien & NIK -->
                        <TableCell>
                            <div class="font-medium text-xs text-slate-900">
                                {{ q.patient?.patient_name }}
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                NIK: {{ q.patient?.nik || '-' }}
                            </div>
                        </TableCell>

                        <!-- Sesi -->
                        <TableCell class="text-xs text-slate-600">
                            {{ formatSession(q.session) }}
                        </TableCell>

                        <!-- Estimasi Jam -->
                        <TableCell>
                            <span class="inline-flex items-center gap-1 font-mono text-xs text-slate-600">
                                <Clock class="size-3 text-slate-400" />
                                {{ q.estimate_time }}
                            </span>
                        </TableCell>

                        <!-- Status: Simple Dot Indicator (Bukan Badge Besar AI) -->
                        <TableCell>
                            <!-- Status: Selesai -->
                            <span v-if="q.queue_status === 'completed'" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="size-1.5 rounded-full bg-emerald-600"></span>
                                Selesai
                            </span>

                            <!-- Status: Diperiksa -->
                            <span v-else-if="q.queue_status === 'checking'" class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700">
                                <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Sedang Diperiksa
                            </span>

                            <!-- Status: Dipanggil -->
                            <span v-else-if="q.queue_status === 'called'" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-700">
                                <span class="size-1.5 rounded-full bg-blue-500"></span>
                                Dipanggil
                            </span>

                            <!-- Status: Menunggu -->
                            <span v-else-if="q.queue_status === 'waiting'" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">
                                <span class="size-1.5 rounded-full bg-slate-400"></span>
                                Menunggu
                            </span>

                            <!-- Status: Batal -->
                            <span v-else class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                <span class="size-1.5 rounded-full bg-rose-500"></span>
                                Batal
                            </span>
                        </TableCell>

                        <!-- Aksi Operasional -->
                        <TableCell class="text-right pr-4">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Status: waiting -> Panggil Pasien -->
                                <Button
                                    v-if="q.queue_status === 'waiting'"
                                    size="sm"
                                    class="h-7 px-2.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-normal"
                                    @click="promptUpdateStatus(q, 'called')"
                                >
                                    <PhoneCall class="size-3 mr-1" />
                                    Panggil
                                </Button>

                                <!-- Status: called -> Masuk Ruang Periksa -->
                                <Button
                                    v-if="q.queue_status === 'called'"
                                    size="sm"
                                    class="h-7 px-2.5 text-xs bg-amber-600 hover:bg-amber-700 text-white font-normal"
                                    @click="handleEnterExaminationRoom(q)"
                                >
                                    <Stethoscope class="size-3 mr-1" />
                                    Periksa
                                </Button>

                                <!-- Status: checking -> Input Rekam Medis -->
                                <Button
                                    v-if="q.queue_status === 'checking'"
                                    size="sm"
                                    class="h-7 px-2.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-normal"
                                    @click="openMedrecModal(q)"
                                >
                                    <FileEdit class="size-3 mr-1" />
                                    Input Rekam Medis
                                </Button>

                                <!-- Batal Antrean dengan Shadcn Tooltip -->
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            v-if="['waiting', 'called'].includes(q.queue_status)"
                                            size="icon-sm"
                                            variant="outline"
                                            class="size-7 text-slate-400 hover:text-rose-600 hover:border-rose-200 cursor-pointer"
                                            @click="promptUpdateStatus(q, 'cancel')"
                                        >
                                            <Ban class="size-3.5" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent
                                        side="top"
                                        :side-offset="6"
                                        class="bg-slate-900 text-white border border-slate-800 text-[11px] font-medium py-1 px-2 rounded-md shadow-lg pointer-events-none select-none"
                                    >
                                        Batalkan Antrean
                                    </TooltipContent>
                                </Tooltip>

                                <span v-if="q.queue_status === 'completed'" class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-normal">
                                    <CheckCircle2 class="size-3.5 text-emerald-600" /> Selesai
                                </span>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="!todayQueues.data || todayQueues.data.length === 0" :colspan="6">
                        <div class="py-10 text-center text-slate-400 text-xs">
                            Tidak ada antrean terdaftar untuk hari ini.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Compact Clean Pagination -->
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-500">
                <span>
                    Hal <strong class="text-slate-700">{{ todayQueues.current_page }}</strong> dari {{ todayQueues.last_page }}
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!todayQueues.prev_page_url"
                        as-child
                    >
                        <Link v-if="todayQueues.prev_page_url" :href="todayQueues.prev_page_url">
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
                        :disabled="!todayQueues.next_page_url"
                        as-child
                    >
                        <Link v-if="todayQueues.next_page_url" :href="todayQueues.next_page_url">
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

        <!-- Modal Rekam Medis (Clean Compact Popup) -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 backdrop-blur-2xs p-4">
            <div class="w-full max-w-2xl max-h-[90vh] bg-white rounded-xl shadow-lg border border-slate-200 flex flex-col overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Input Rekam Medis Pasien</h2>
                        <p class="text-[11px] text-slate-500">
                            {{ activeQueue?.patient?.patient_name }} (Tiket #{{ activeQueue?.queue_number }})
                        </p>
                    </div>
                    <button type="button" @click="closeModal" class="text-slate-400 hover:text-slate-600 rounded p-1">
                        <X class="size-4" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 overflow-y-auto space-y-4 text-xs">
                    <!-- Alert Error Transaksi / Validasi Umum -->
                    <div
                        v-if="form.errors.error_transaction || $page.props.errors?.error_transaction"
                        class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2"
                    >
                        <AlertCircle class="size-4 shrink-0 text-rose-600" />
                        <span>{{ form.errors.error_transaction || $page.props.errors?.error_transaction }}</span>
                    </div>

                    <form @submit.prevent="submitMedicalRecord" id="medrecForm" class="space-y-3.5">
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Keluhan Utama <span class="text-rose-500">*</span></label>
                            <textarea
                                v-model="form.main_complaint"
                                required
                                rows="2"
                                placeholder="Keluhan sakit yang dirasakan..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                            ></textarea>
                            <span v-if="form.errors.main_complaint" class="text-rose-500 text-[10px]">{{ form.errors.main_complaint }}</span>
                        </div>

                        <!-- 3 Kolom Vital Signs -->
                        <div class="grid grid-cols-3 gap-2">
                            <div class="space-y-1">
                                <label class="text-slate-600">Tensi (mmHg)</label>
                                <input
                                    type="text"
                                    v-model="form.blood_pressure"
                                    placeholder="120/80"
                                    class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                                />
                                <span v-if="form.errors.blood_pressure" class="text-rose-500 text-[10px]">{{ form.errors.blood_pressure }}</span>
                            </div>
                            <div class="space-y-1">
                                <label class="text-slate-600">Suhu (°C)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    v-model="form.temperature"
                                    placeholder="36.5"
                                    class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                                />
                                <span v-if="form.errors.temperature" class="text-rose-500 text-[10px]">{{ form.errors.temperature }}</span>
                            </div>
                            <div class="space-y-1">
                                <label class="text-slate-600">Berat (Kg)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    v-model="form.weight"
                                    placeholder="60"
                                    class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                                />
                                <span v-if="form.errors.weight" class="text-rose-500 text-[10px]">{{ form.errors.weight }}</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Diagnosa Medis <span class="text-rose-500">*</span></label>
                            <textarea
                                v-model="form.diagnosis"
                                required
                                rows="2"
                                placeholder="Diagnosa dokter..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                            ></textarea>
                            <span v-if="form.errors.diagnosis" class="text-rose-500 text-[10px]">{{ form.errors.diagnosis }}</span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-slate-600">Tindakan / Catatan Tambahan</label>
                            <textarea
                                v-model="form.notes_on_treatment"
                                rows="2"
                                placeholder="Tindakan atau anjuran istirahat..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <!-- Resep Obat -->
                        <div class="pt-2 border-t border-slate-100 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="font-semibold text-slate-700">Resep Obat</label>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="h-6 px-2 text-[11px] gap-1"
                                    @click="addPrescription"
                                >
                                    <Plus class="size-3" /> Tambah Obat
                                </Button>
                            </div>

                            <div v-if="form.prescriptions.length > 0" class="space-y-1.5">
                                <div
                                    v-for="(item, index) in form.prescriptions"
                                    :key="index"
                                    class="p-2 rounded-lg bg-slate-50 border border-slate-200 grid grid-cols-12 gap-2 items-center"
                                >
                                    <div class="col-span-6">
                                        <Select
                                            :model-value="item.medication_id ? String(item.medication_id) : ''"
                                            @update:model-value="(val) => item.medication_id = Number(val)"
                                        >
                                            <SelectTrigger class="w-full h-8 text-xs bg-white border-slate-200">
                                                <SelectValue placeholder="Pilih Obat" />
                                            </SelectTrigger>
                                            <SelectContent class="bg-white border-slate-200 max-h-56 z-[60]">
                                                <SelectItem
                                                    v-for="med in props.medications"
                                                    :key="med.medication_id"
                                                    :value="String(med.medication_id)"
                                                    class="text-xs"
                                                >
                                                    {{ med.medication_name }} (Stok: {{ med.stock }})
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="col-span-4">
                                        <input
                                            type="text"
                                            v-model="item.dosage"
                                            placeholder="Dosis (e.g. 3x1)"
                                            required
                                            class="w-full h-8 rounded border border-slate-200 bg-white px-2.5 text-xs text-slate-800 focus:outline-none focus:border-indigo-500"
                                        />
                                    </div>
                                    <div class="col-span-1">
                                        <input
                                            type="number"
                                            v-model="item.qty"
                                            min="1"
                                            required
                                            class="w-full h-8 rounded border border-slate-200 bg-white px-1 text-xs text-slate-800 text-center focus:outline-none focus:border-indigo-500"
                                        />
                                    </div>
                                    <div class="col-span-1 text-center">
                                        <button
                                            type="button"
                                            class="text-slate-400 hover:text-rose-600 transition"
                                            @click="removePrescription(index)"
                                        >
                                            <Trash2 class="size-3.5" />
                                        </button>
                                    </div>
                                    <div v-if="form.errors[`prescriptions.${index}.medication_id`] || form.errors[`prescriptions.${index}.dosage`] || form.errors[`prescriptions.${index}.qty`]" class="col-span-12 text-[10px] text-rose-500">
                                        {{ form.errors[`prescriptions.${index}.medication_id`] || form.errors[`prescriptions.${index}.dosage`] || form.errors[`prescriptions.${index}.qty`] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/80 flex items-center justify-end gap-2">
                    <Button type="button" variant="outline" size="sm" @click="closeModal">Batal</Button>
                    <Button
                        type="submit"
                        form="medrecForm"
                        size="sm"
                        :disabled="form.processing"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Rekam Medis' }}
                    </Button>
                </div>
            </div>
        </div>

        <!-- Spring / Bounce Modal Konfirmasi Perubahan Status Antrean -->
        <SpringModal
            :open="isStatusModalOpen"
            @update:open="isStatusModalOpen = $event"
            :title="statusModalData?.title || 'Ubah Status Antrean'"
            :description="`Konfirmasi perubahan status nomor antrean ${statusModalData?.queue?.queue_number}.`"
        >
            <div class="py-2 text-xs text-slate-600 leading-relaxed">
                <template v-if="statusModalData?.newStatus === 'cancel'">
                    <div class="p-3 bg-rose-50/70 border border-rose-100 rounded-lg text-rose-700">
                        Apakah Anda yakin ingin <strong class="font-bold">membatalkan</strong> antrean pasien
                        <strong>"{{ statusModalData?.queue?.patient?.patient_name }}"</strong>
                        (No: {{ statusModalData?.queue?.queue_number }})? Pasien tidak akan dipanggil lagi hari ini.
                    </div>
                </template>
                <template v-else>
                    <div class="p-3 bg-indigo-50/70 border border-indigo-100 rounded-lg text-indigo-900">
                        Ubah status antrean <strong>"{{ statusModalData?.queue?.patient?.patient_name }}"</strong>
                        (No: {{ statusModalData?.queue?.queue_number }}) menjadi
                        <strong class="font-bold text-indigo-700">"{{ statusModalData?.label }}"</strong>?
                    </div>
                </template>
            </div>

            <template #footer>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-8 text-xs border-slate-200"
                    :disabled="isUpdatingStatus"
                    @click="isStatusModalOpen = false"
                >
                    Batal
                </Button>
                <Button
                    type="button"
                    :variant="statusModalData?.newStatus === 'cancel' ? 'destructive' : 'default'"
                    size="sm"
                    class="h-8 text-xs"
                    :class="statusModalData?.newStatus === 'cancel' ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-indigo-600 hover:bg-indigo-700 text-white'"
                    :disabled="isUpdatingStatus"
                    @click="confirmUpdateStatus"
                >
                    {{ isUpdatingStatus ? 'Memproses...' : (statusModalData?.newStatus === 'cancel' ? 'Ya, Batalkan' : 'Ya, Ubah') }}
                </Button>
            </template>
        </SpringModal>
    </div>
</template>
