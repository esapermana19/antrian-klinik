<script setup>
import { ref } from 'vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import queueRoute from '@/routes/queue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Calendar as CalendarIcon,
    Clock,
    X,
    Info,
} from 'lucide-vue-next';

const props = defineProps({
    patients: Array,
    schedulesData: Array,
    date: String,
    errors: Object,
});

const filterDate = ref(props.date);

const changeDate = () => {
    router.get(
        queueRoute.index.url(),
        { date: filterDate.value },
        { preserveState: true },
    );
};

const selectedPatientMode = ref('new');

const bookingForm = useForm({
    patient_id: '',
    schedule_id: '',
    queue_date: '',
    time: '',
    queue_number: '',
    nik: '',
    patient_name: '',
    date_of_birth: '',
    gender: 'male',
    address: '',
});

const onSelectPatientChange = (value) => {
    selectedPatientMode.value = value;
    if (value === 'new') {
        bookingForm.patient_id = '';
        bookingForm.nik = '';
        bookingForm.patient_name = '';
        bookingForm.date_of_birth = '';
        bookingForm.gender = 'male';
        bookingForm.address = '';
    } else {
        const patient = props.patients.find(
            (p) => p.patient_id === Number(value),
        );
        if (patient) {
            bookingForm.patient_id = patient.patient_id;
            bookingForm.nik = patient.nik;
            bookingForm.patient_name = patient.patient_name;
            bookingForm.date_of_birth = patient.date_of_birth;
            bookingForm.gender = patient.gender;
            bookingForm.address = patient.address || '';
        }
    }
};

const isModalOpen = ref(false);

const clickSlot = (scheduleId, slotTime, qNumber, status) => {
    if (['booked', 'locked', 'passed'].includes(status)) return;

    if (status === 'locked_by_me') {
        bookingForm.schedule_id = scheduleId.toString();
        bookingForm.queue_date = props.date;
        bookingForm.time = slotTime;
        bookingForm.queue_number = qNumber;
        isModalOpen.value = true;
        return;
    }

    router.post(
        queueRoute.lock.url(),
        {
            schedule_id: scheduleId,
            date: props.date,
            time: slotTime,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                bookingForm.schedule_id = scheduleId.toString();
                bookingForm.queue_date = props.date;
                bookingForm.time = slotTime;
                bookingForm.queue_number = qNumber;
                isModalOpen.value = true;
            },
        },
    );
};

const cancelBooking = () => {
    router.post(
        queueRoute.unlock.url(),
        {
            schedule_id: bookingForm.schedule_id,
            date: bookingForm.queue_date,
            time: bookingForm.time,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                bookingForm.reset();
            },
        },
    );
};

const submitBooking = () => {
    bookingForm.post(queueRoute.store.url(), {
        onSuccess: () => {
            isModalOpen.value = false;
            bookingForm.reset();
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

const minDate = new Date().toISOString().split('T')[0];

const getSlotStyle = (status) => {
    switch (status) {
        case 'available':
            return 'bg-white border-slate-200 text-slate-800 hover:border-indigo-500 hover:bg-slate-50/50 cursor-pointer shadow-2xs';
        case 'locked_by_me':
            return 'bg-amber-50 border-amber-300 text-amber-900 cursor-pointer ring-1 ring-amber-400 shadow-2xs';
        case 'booked':
            return 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed opacity-60';
        case 'locked':
            return 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed opacity-60';
        case 'passed':
            return 'bg-slate-100/70 border-slate-200 text-slate-400 cursor-not-allowed opacity-50';
        default:
            return 'bg-white border-slate-200 text-slate-700';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'available':
            return { text: 'Tersedia', class: 'text-emerald-700 font-medium' };
        case 'locked_by_me':
            return { text: 'Dipilih Anda', class: 'text-amber-800 font-semibold' };
        case 'booked':
            return { text: 'Terisi', class: 'text-slate-400' };
        case 'locked':
            return { text: 'Sedang Diisi', class: 'text-slate-400' };
        case 'passed':
            return { text: 'Terlewat', class: 'text-slate-400' };
        default:
            return { text: status, class: 'text-slate-500' };
    }
};
</script>

<template>
    <Head title="Ambil Antrean Pasien" />

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Ambil Nomor Antrean</h1>
                <p class="text-xs text-slate-500">Pilih tanggal periksa dan slot antrean dokter yang masih tersedia.</p>
            </div>

            <!-- Shadcn Date Picker Style -->
            <div class="relative flex items-center">
                <div class="flex items-center gap-2 h-9 px-3 rounded-md border border-slate-200 bg-white text-xs shadow-2xs">
                    <CalendarIcon class="size-3.5 text-slate-500" />
                    <span class="text-slate-500 font-medium">Tanggal Booking:</span>
                    <input
                        type="date"
                        v-model="filterDate"
                        @change="changeDate"
                        :min="minDate"
                        class="text-xs font-mono font-medium text-slate-900 bg-transparent border-0 p-0 focus:outline-none cursor-pointer"
                    />
                </div>
            </div>
        </div>

        <!-- Legend / Petunjuk Status Slot -->
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600 px-1">
            <div class="flex items-center gap-1.5">
                <span class="size-2 rounded-full border border-slate-300 bg-white"></span>
                <span>Tersedia</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="size-2 rounded-full bg-amber-400"></span>
                <span>Dipilih Anda</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="size-2 rounded-full bg-slate-300"></span>
                <span>Terisi / Terlewat</span>
            </div>
        </div>

        <!-- Schedules Grid -->
        <div v-if="props.schedulesData && props.schedulesData.length > 0" class="space-y-3.5">
            <Card
                v-for="data in props.schedulesData"
                :key="data.schedules.schedule_id"
                class="p-4 space-y-3 rounded-lg border-slate-200"
            >
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <Clock class="size-3.5 text-indigo-600" />
                        <h3 class="text-xs font-bold text-slate-900">
                            Dokter Sesi {{ formatSession(data.schedules.session) }}
                        </h3>
                    </div>
                    <span class="text-xs font-mono text-slate-500">
                        {{ data.schedules.opening_hours?.substring(0, 5) }} - {{ data.schedules.closing_hours?.substring(0, 5) }}
                    </span>
                </div>

                <!-- Slots List (Proporsional & Compact) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                    <button
                        v-for="slot in data.slots"
                        :key="slot.time"
                        type="button"
                        @click="clickSlot(data.schedules.schedule_id, slot.time, slot.queue_number, slot.status)"
                        :disabled="['booked', 'locked', 'passed'].includes(slot.status)"
                        class="p-2 rounded-md border text-center transition flex flex-col items-center justify-center gap-0.5"
                        :class="getSlotStyle(slot.status)"
                    >
                        <span class="font-mono text-xs font-semibold whitespace-nowrap">
                            {{ slot.queue_number }}
                        </span>
                        <span class="font-mono text-[11px] text-slate-500">
                            {{ slot.time?.substring(0, 5) }}
                        </span>
                        <span class="text-[10px]" :class="getStatusLabel(slot.status).class">
                            {{ getStatusLabel(slot.status).text }}
                        </span>
                    </button>
                </div>
            </Card>
        </div>

        <!-- Empty Schedule -->
        <Card v-else class="p-8 text-center text-slate-400 text-xs rounded-lg border-slate-200">
            <Info class="size-7 mx-auto mb-2 text-slate-300" />
            <p class="font-semibold text-slate-700">Tidak ada jadwal dokter untuk tanggal ini</p>
            <p class="text-slate-400 mt-0.5">Silakan pilih tanggal lain pada pemilih tanggal di atas.</p>
        </Card>

        <!-- Modal Booking Antrean (Shadcn UI Dropdown / Select) -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 backdrop-blur-2xs p-4">
            <div class="w-full max-w-md bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Konfirmasi Booking Antrean</h2>
                        <p class="text-[11px] text-slate-500">
                            Tiket: <span class="font-mono font-semibold text-slate-900">{{ bookingForm.queue_number }}</span> • Jam: {{ bookingForm.time }}
                        </p>
                    </div>
                    <button type="button" @click="cancelBooking" class="text-slate-400 hover:text-slate-600 rounded p-1">
                        <X class="size-4" />
                    </button>
                </div>

                <form @submit.prevent="submitBooking" class="p-4 space-y-3 text-xs">
                    <!-- Shadcn Select Pasien -->
                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Pilih Pasien</label>
                        <Select :model-value="selectedPatientMode" @update:model-value="onSelectPatientChange">
                            <SelectTrigger class="w-full h-8 text-xs bg-white border-slate-200">
                                <SelectValue placeholder="Pilih Pasien" />
                            </SelectTrigger>
                            <SelectContent class="bg-white border-slate-200">
                                <SelectItem value="new" class="text-xs font-medium text-indigo-600">
                                    + Daftarkan Pasien Baru
                                </SelectItem>
                                <SelectItem
                                    v-for="p in props.patients"
                                    :key="p.patient_id"
                                    :value="String(p.patient_id)"
                                    class="text-xs"
                                >
                                    {{ p.patient_name }} (NIK: {{ p.nik }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Nomor Induk Kependudukan (NIK)</label>
                        <input
                            type="text"
                            v-model="bookingForm.nik"
                            maxlength="16"
                            required
                            placeholder="16 digit NIK"
                            class="w-full h-8 rounded-md border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                        />
                        <span v-if="bookingForm.errors.nik" class="text-[10px] text-rose-500">{{ bookingForm.errors.nik }}</span>
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Nama Lengkap Pasien</label>
                        <input
                            type="text"
                            v-model="bookingForm.patient_name"
                            required
                            placeholder="Nama pasien..."
                            class="w-full h-8 rounded-md border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                        />
                        <span v-if="bookingForm.errors.patient_name" class="text-[10px] text-rose-500">{{ bookingForm.errors.patient_name }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Tanggal Lahir</label>
                            <input
                                type="date"
                                v-model="bookingForm.date_of_birth"
                                required
                                class="w-full h-8 rounded-md border border-slate-200 px-3 py-1 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none cursor-pointer"
                            />
                            <span v-if="bookingForm.errors.date_of_birth" class="text-[10px] text-rose-500">{{ bookingForm.errors.date_of_birth }}</span>
                        </div>

                        <!-- Shadcn Select Gender -->
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Jenis Kelamin</label>
                            <Select :model-value="bookingForm.gender" @update:model-value="(val) => bookingForm.gender = val">
                                <SelectTrigger class="w-full h-8 text-xs bg-white border-slate-200">
                                    <SelectValue placeholder="Pilih Gender" />
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
                            v-model="bookingForm.address"
                            rows="2"
                            placeholder="Alamat domisili..."
                            class="w-full rounded-md border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <Button type="button" variant="outline" size="sm" @click="cancelBooking">
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            size="sm"
                            :disabled="bookingForm.processing"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white"
                        >
                            {{ bookingForm.processing ? 'Memproses...' : 'Konfirmasi Booking' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
