<script setup>
import { ref } from 'vue';
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
});

const isModalOpen = ref(false);
const activeQueue = ref(null);

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
    form.reset();
    form.patient_id = queue.patient_id ?? queue.patient?.patient_id;
    form.queue_id = queue.queue_id;
    form.main_complaint = '';
    form.blood_pressure = '';
    form.temperature = '';
    form.weight = '';
    form.diagnosis = '';
    form.notes_on_treatment = '';
    form.prescriptions = [];
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    activeQueue.value = null;
    form.reset();
    form.clearErrors();
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
            closeModal();
        },
    });
};

const updateStatus = (queueId, newStatus) => {
    if (confirm(`Ubah status antrean menjadi ${newStatus}?`)) {
        router.patch(`/queue-management/${queueId}/status`, {
            queue_status: newStatus,
        }, {
            preserveScroll: true,
        });
    }
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
            <Button variant="outline" size="sm" as-child>
                <Link href="/display-queue" target="_blank" class="gap-1.5 text-xs text-slate-700">
                    <Tv class="size-3.5 text-indigo-600" />
                    Buka Display TV
                </Link>
            </Button>
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
                                    @click="updateStatus(q.queue_id, 'called')"
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

                                <!-- Batal Antrean -->
                                <Button
                                    v-if="['waiting', 'called'].includes(q.queue_status)"
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 text-slate-400 hover:text-rose-600 hover:border-rose-200"
                                    title="Batalkan Antrean"
                                    @click="updateStatus(q.queue_id, 'cancel')"
                                >
                                    <Ban class="size-3.5" />
                                </Button>

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
                                        <select
                                            v-model="item.medication_id"
                                            required
                                            class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800"
                                        >
                                            <option value="" disabled>Pilih Obat</option>
                                            <option
                                                v-for="med in props.medications"
                                                :key="med.medication_id"
                                                :value="med.medication_id"
                                            >
                                                {{ med.medication_name }} (Stok: {{ med.stock }})
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-span-4">
                                        <input
                                            type="text"
                                            v-model="item.dosage"
                                            placeholder="Dosis (e.g. 3x1)"
                                            required
                                            class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800"
                                        />
                                    </div>
                                    <div class="col-span-1">
                                        <input
                                            type="number"
                                            v-model="item.qty"
                                            min="1"
                                            required
                                            class="w-full rounded border border-slate-200 bg-white px-1 py-1 text-xs text-slate-800 text-center"
                                        />
                                    </div>
                                    <div class="col-span-1 text-center">
                                        <button
                                            type="button"
                                            class="text-slate-400 hover:text-rose-600"
                                            @click="removePrescription(index)"
                                        >
                                            <Trash2 class="size-3.5" />
                                        </button>
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
    </div>
</template>
