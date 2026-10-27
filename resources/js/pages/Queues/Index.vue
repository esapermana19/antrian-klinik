<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import queueRoute from '@/routes/queue';

const props = defineProps({
    patients: Object,
    schedulesData: Array,
    date: String,
    errors: Object,
});

const filterDate = ref(props.date);

const changeDate = () => {
    router.get(queueRoute.index.url(), { date: filterDate.value }, { preserveState: true });
};

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

    //jika belum dikunci, kunci sekarang
    router.post(queueRoute.lock.url(), {
        schedule_id: scheduleId,
        date: props.date,
        time: slotTime,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            bookingForm.schedule_id = scheduleId.toString();
            bookingForm.queue_date = props.date;
            bookingForm.time = slotTime;
            bookingForm.queue_number = qNumber;
            isModalOpen.value = true;
        }
    });
};

//fungsi batal lock
const cancelBooking = () => {
    router.post(queueRoute.unlock.url(), {
        schedule_id: bookingForm.schedule_id,
        date: bookingForm.queue_date,
        time: bookingForm.time,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            bookingForm.reset();
        }
    });
};

const submitBooking = () => {
    bookingForm.post(queueRoute.store.url(), {
        onSuccess: () => {
            isModalOpen.value = false;
            bookingForm.reset();
        },
    });
};

const updateStatus = (queueId, newStatus) => {
    router.patch(queueRoute.updateStatus.url, {
        queue_status: newStatus
    });
};

const getSlotColor = (status) => {
    switch(status) {
        case 'available': return 'lightgreen';
        case 'locked_by_me': return 'yellow';
        case 'locked': return 'lightgray';
        case 'booked': return 'red';
        case 'passed': return '#d3d3d3';
        default: return 'white';
    }
}

const formatSession  = (session) => {
    const sessionName = {
        morning: 'Pagi',
        afternoon: 'Sore'
    };
    return sessionName[session] ||session;
};

const minDate = new Date().toISOString().split('T')[0];

</script>

<template>
    <div>
        <h1>pilih jadwal dan antreank</h1>

        <div style="margin-bottom: 20px;">
            <label>Cari Tanggal Booking: </label>
            <input type="date" v-model="filterDate" @change="changeDate" :min="minDate">
        </div>

        <div v-for="data in props.schedulesData" :key="data.schedules.schedule_id" style="margin-bottom: 30px;">
            <h3>Dokter Sesi {{ formatSession(data.schedules.session) }} ({{ data.schedules.opening_hours }} - {{
                data.schedules.closing_hours }})</h3>

            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <button v-for="slot in data.slots" :key="slot.time"
                    @click="clickSlot(data.schedules.schedule_id, slot.time, slot.queue_number, slot.status)"
                    :disabled="['booked', 'locked', 'passed'].includes(slot.status)"
                    :style="{
                        padding: '15px',
                        border: '1px solid black',
                        cursor: (['booked', 'locked', 'passed'].includes(slot.status) ? 'not-allowed' : 'pointer'),
                        backgroundColor: getSlotColor(slot.status)
                    }">
                    <strong>{{ slot.queue_number }}</strong><br>
                    <strong>{{ slot.time }}</strong><br>

                    <small v-if="slot.status === 'available'">Tersedia</small>
                    <small v-else-if="slot.status === 'locked_by_me'">Draft Anda</small>
                    <small v-else-if="slot.status === 'locked'">Sedang di isi</small>
                    <small v-else-if="slot.status === 'booked'">Penuh</small>
                    <small v-else-if="slot.status === 'passed'">Waktu Lewat</small>
                </button>
            </div>
        </div>
        <div v-if="props.schedulesData.length === 0" style="padding: 20px; background-color: #f8f9fa; border: 1px solid #ddd;">
            <p><strong>Tidak ada jadwal praktek hari ini</strong></p>
            <small>Silakan pilih tanggal lain</small>
        </div>

        <div v-if="isModalOpen" style="margin-top: 30px; padding: 20px; border: 2px dashed blue;">
            <h2>Lengkapi data booking</h2>
            <p>Nomor Antrean: <strong>{{ bookingForm.queue_number }}</strong> | Jam: <strong>{{ bookingForm.time
                    }}</strong></p>

            <form @submit.prevent="submitBooking">
                <div>
                    <label>NIK: </label><br>
                    <input type="text" v-model="bookingForm.nik" maxlength="16" required placeholder="Masukkan 16 digit NIK">
                    <div v-if="bookingForm.errors.nik" style="color: red;">{{ bookingForm.errors.nik }}</div>
                </div>
                <div>
                    <label>Nama Pasien: </label><br>
                    <input type="text" v-model="bookingForm.patient_name" maxlength="16" required placeholder="Masukkan Nama Lengkap Pasien">
                    <div v-if="bookingForm.errors.patient_name" style="color: red;">{{ bookingForm.errors.patient_name }}</div>
                </div>
                <div>
                    <label>Tanggal Lahir: </label><br>
                    <input type="date" v-model="bookingForm.date_of_birth" maxlength="16" required placeholder="Masukkan Nama Lengkap Pasien">
                    <div v-if="bookingForm.errors.date_of_birth" style="color: red;">{{ bookingForm.errors.date_of_birth }}</div>
                </div>
                <div>
                    <label>Jenis Kelamin: </label><br>
                    <select v-model="bookingForm.gender" required>
                        <option value="male">Laki-laki</option>
                        <option value="female">Perempuan</option>
                    </select>
                    <div v-if="bookingForm.errors.gender" style="color: red;">{{ bookingForm.errors.gender }}</div>
                </div>
                <div>
                    <label>Alamat</label>
                    <textarea v-model="bookingForm.address"></textarea>
                    <div v-if="bookingForm.errors.address" style="color: red;">{{ bookingForm.errors.address }}</div>
                </div>
                <br>
                <button type="submit" :disabled="bookingForm.processing">Konfirmasi Booking</button>
                <button type="button" @click="cancelBooking" style="margin-left: 10px;">Batal</button>
            </form>
        </div>
    </div>
</template>
