<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    medications: Object,
    errors: Object,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMedicationId = ref(null);

const form = useForm({
    medication_name: '',
    stock: '',
    unit_price: '',
});

const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (medication) => {
    isEditMode.value = true;
    selectedMedicationId.value = medication.medication_id ?? medication.id;
    form.clearErrors();

    form.medication_name = medication.medication_name;
    form.stock = medication.stock;
    form.unit_price = medication.unit_price;

    isModalOpen.value = true;
};

 const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
 };

 const submitForm = () => {
    if (isEditMode.value) {
        form.put(`/medication/${selectedMedicationId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/medication', {
            onSuccess: () => closeModal(),
        });
    }
 };

 const deleteMedication = (medication) => {
    const id = medication.medication_id ?? medication.id;
    if (
        confirm(`Hapus data obat?`)
    ) {
        router.delete(`/medication/${id}`);
    }
 };


</script>

<template>
    <div>
        <h1>Master Data Obat</h1>
        <p>Kelola ketersediaan obat di klinik.</p>
        <button type="button" @click="openAddModal">+ Tambah Obat</button>
        <hr/>

        <table cellpadding="20" cellspacing="0">
            <thead>
                <tr>
                    <th>Kategori Obat</th>
                    <th>Kode Obat</th>
                    <th>Nama Obat</th>
                    <th>Stok</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in medications.data" :key="item.medication_id ?? item.id">
                    <td>{{ item.medication_category?.medication_name ?? '-' }}</td>
                    <td>{{ item.medication_code }}</td>
                    <td>{{ item.medication_name }}</td>
                    <td>{{ item.stock }}</td>
                    <td>Rp. {{ item.unit_price }}</td>
                    <td>
                        <button type="button" @click="openEditModal(item)">
                            Edit
                        </button>
                        <button type="button" @click="deleteMedication(item)">
                            Hapus
                        </button>
                    </td>
                </tr>
                <tr v-if="medications.data.length === 0">
                    <td colspan="5">Belum ada data obat</td>
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
