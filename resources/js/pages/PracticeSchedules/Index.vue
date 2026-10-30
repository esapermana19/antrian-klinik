<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import scheduleRoute from '@/routes/practice-schedule';
import medication from '@/routes/medication';

const props = defineProps({
    schedules: Object,
    errors: Object,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedScheduleId = ref(null);

const form = useForm({
    day: '',
    session: '',
    opening_hours: '',
    closing_hours: '',
    max_quota: '',
    status: 'open',
});

const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (schedule) => {
    isEditMode.value = true;
    selectedScheduleId.value = schedule.schedule_id ?? schedule.id;
    form.clearErrors();

    form.day = schedule.day;
    form.session = schedule.session;
    form.opening_hours = schedule.opening_hours.substring(0, 5);
    form.closing_hours = schedule.closing_hours.substring(0, 5);
    form.status = schedule.status;

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(scheduleRoute.update.url(selectedScheduleId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(scheduleRoute.store.url(), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSchedule = (schedule) => {
    const id = schedule.schedule_id ?? schedule.id;
    if (
        confirm(`Hapus jadwal hari ${schedule.day} sesi ${schedule.session}?`)
    ) {
        router.delete(scheduleRoute.destroy.url(id));
    }
};

const formatDay = (day) => {
    const dayName = {
        monday: 'Senin',
        tuesday: 'Selasa',
        wednesday: 'Rabu',
        thursday: 'Kamis',
        friday: 'Jumat',
        saturday: 'Sabtu',
        sunday: 'Minggu',
    };
    return dayName[day] || day;
};
</script>

<template>
    <div>
        <h1>Master Jadwal Praktik</h1>
        <p>Kelola sesi praktik rutin mingguan dan batas kuota pasien.</p>

        <button type="button" @click="openAddModal">+ Tambah Jadwal</button>
        <hr />

        <!-- Tabel data jadwal -->
        <table border="3" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode</th>
                    <th>Hari</th>
                    <th>Sesi</th>
                    <th>Jam Praktik</th>
                    <th>Kuota</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in schedules.data" :key="item.schedule_id ?? item.id">
                    <td>{{ (schedules.current_page -1) * (schedules. per_page) + (index + 1) }}</td>
                    <td>{{ item.schedule_code }}</td>
                    <td>{{ formatDay(item.day) }}</td>
                    <td>{{ item.session === 'morning' ? 'pagi' : 'sore' }}</td>
                    <td>
                        {{ item.opening_hours.substring(0, 5) }} -
                        {{ item.closing_hours.substring(0, 5) }}
                    </td>
                    <td>{{ item.max_quota }} Pasien</td>
                    <td>{{ item.status }}</td>
                    <td>
                        <button type="button" @click="openEditModal(item)">
                            Edit
                        </button>
                        <button type="button" @click="deleteSchedule(item)">
                            Hapus
                        </button>
                    </td>
                </tr>
                <tr v-if="schedules.data.length === 0">
                    <td colspan="7">Belum ada jadwal yang dirambahkan</td>
                </tr>
            </tbody>
        </table>

        <div>
            <Link v-if="schedules.prev_page_url" :href="schedules.prev_page_url">
                Previous
            </Link>
            <button v-else disabled style="border: 1px solid black;">Previous</button>

            <span>
                Halaman {{ schedules.current_page }} dari {{ schedules.last_page }}
            </span>

            <Link v-if="schedules.next_page_url" :href="schedules.next_page_url">
                Next
            </Link>
            <button v-else disabled style="border: 1px solid black;">Next</button>
        </div>

        <!-- Form tambah jadwal -->
        <div v-if="isModalOpen">
            <hr />
            <h2>
                {{ isEditMode ? 'Edit Jadwal Praktik' : 'Tambah Jadwal Baru' }}
            </h2>

            <form @submit.prevent="submitForm">
                <div>
                    <label>Hari: </label>
                    <select v-model="form.day">
                        <option value="">-- Pilih Hari --</option>
                        <option value="monday">Senin</option>
                        <option value="tuesday">Selasa</option>
                        <option value="wednesday">Rabu</option>
                        <option value="thursday">Kamis</option>
                        <option value="friday">Jumat</option>
                        <option value="saturday">Sabtu</option>
                        <option value="sunday">Minggu</option>
                    </select>
                    <span v-if="form.errors.day" style="color: red">{{
                        form.errors.day
                        }}</span>
                </div>

                <div>
                    <label>Sesi: </label>
                    <select v-model="form.session">
                        <option value="">-- Pilih Sesi --</option>
                        <option value="morning">Pagi</option>
                        <option value="afternoon">Sore</option>
                    </select>
                    <span v-if="form.errors.session" style="color: red">{{
                        form.errors.session
                        }}</span>
                </div>

                <div>
                    <label>Jam Buka: </label>
                    <input type="time" v-model="form.opening_hours" lang="id-ID" step="60" />
                    <span v-if="form.errors.opening_hours" style="color: red">{{
                        form.errors.opening_hours
                        }}</span>
                </div>
                <div>
                    <label>Jam Tutup: </label>
                    <input type="time" v-model="form.closing_hours" lang="id-ID" step="60" />
                    <span v-if="form.errors.closing_hours" style="color: red">{{
                        form.errors.closing_hours
                        }}</span>
                </div>

                <div>
                    <label>Status: </label>
                    <select v-model="form.status">
                        <option value="open">Buka</option>
                        <option value="closed">Tutup</option>
                    </select>
                    <span v-if="form.errors.status" style="color: red">{{
                        form.errors.status
                        }}</span>
                </div>

                <br />
                <div>
                    <button type="button" @click="closeModal">Batal</button>
                    <button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th,
td {
    padding: 6px 10px !important;
    text-align: left;
    border: 1px solid #ccc !important;
}

form>div {
    margin-bottom: 12px;
}

label {
    display: inline-block;
    min-width: 110px;
}

input[type='time'],
input[type='number'],
input[type='text'],
select {
    border: 1px solid #777 !important;
}

button {
    border: 1px solid #777 !important;
    margin-right: 8px;
}
</style>
