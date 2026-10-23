<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    patients: Object,
    errors: Object,
    users: Array,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedPatientId = ref(null);

const form = useForm({
    user_id: '',
    nik: '',
    patient_name: '',
    date_of_birth: '',
    gender: '',
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
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(`/patient/${selectedPatientId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/patient', {
            onSuccess: () => closeModal(),
        });
    }
};

const deletePatient = (patient) => {
    const id = patient.patient_id ?? patient.id;
    if (confirm(`hapus data pasien?`)) {
        router.delete(`/patient/${id}`);
    }
};

const formatGender = (day) => {
    const genderName = {
        male: 'Laki-laki',
        female: 'Perempuan'
    };
    return genderName[gender] || gender;
};
</script>

<template>
    <div>
        <h1>Master Data Pasien</h1>
        <p>Kelola data pasien di klinik.</p>
        <button type="button" @click="openAddModal">+ Tambah Data</button>

        <table cellpadding="20" cellspacing="0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIK</th>
                    <th>Nama Pasien</th>
                    <th>Tanggal Lahir</th>
                    <th>Jenis Kelamin</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(item, index) in patients.data" :key="item.patient_id ?? item.id">
                    <td>{{ (patients.current_page - 1) * patients.per_page + (index + 1) }}</td>
                    <td>{{ item.nik }}</td>
                    <td>{{ item.patient_name }}</td>
                    <td>{{ item.date_of_birth }}</td>
                    <td>{{ item.gender }}</td>
                    <td>{{ item.address }}</td>
                    <td>
                        <button type="button" @click="openEditModal">Edit</button>
                        <button type="button" @click="deletePatient">Hapus</button>
                    </td>
                </tr>
                <tr v-if="patients.data.length === 0">
                    <td colspan="5">Belum ada data pasien</td>
                </tr>
            </tbody>
        </table>
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

form > div {
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
