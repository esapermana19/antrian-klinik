<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    medication_categories: Object,
    errors: Object,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMedicationCategoryId = ref(null);

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
        form.put(`/medication-category/${selectedMedicationCategoryId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/medication-category', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteMedicationCategory = (dataCategory) => {
    const id = dataCategory.medication_category_id ?? dataCategory.id;
    if (confirm(`Hapus kategori obat?`)) {
        router.delete(`/medication-category/${id}`);
    }
};
</script>
<template>
    <div>
        <h1>Master Kategori Obat</h1>
        <p>Kelola kategori obat untuk mempermudah pengelolaan data obat.</p>
        <button type="button" @click="openAddModal">
            + Tambah Kategori Obat
        </button>
        <hr />

        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Kategori Obat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="item in medication_categories.data"
                    :key="item.medication_category_id ?? item.id"
                >
                    <td>{{ item.medication_category_code }}</td>
                    <td>{{ item.medication_category_name }}</td>
                    <td>
                        <button type="button" @click="openEditModal(item)">
                            Edit
                        </button>
                        <button
                            type="button"
                            @click="deleteMedicationCategory(item)"
                        >
                            Hapus
                        </button>
                    </td>
                </tr>
                <tr v-if="medication_categories.data.length === 0">
                    <td colspan="7">
                        Belum ada kategori obat yang ditambahkan.
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="isModalOpen">
            <hr />
            <h2>
                {{ isEditMode ? 'Edit kategori obat' : 'Tambah kategori obat' }}
            </h2>
            <form @submit.prevent="submitForm">
                <div>
                    <label>Kode Kategori: </label>
                    <input
                        type="text"
                        v-model="form.medication_category_code"
                        size="1"
                    />
                    <span
                        v-if="form.errors.medication_category_code"
                        style="color: red"
                    >
                        {{ form.errors.medication_category_code }}
                    </span>
                </div>
                <div>
                    <label>Nama Kategori: </label>
                    <input
                        type="text"
                        v-model="form.medication_category_name"
                    />
                    <span
                        v-if="form.errors.medication_category_name"
                        style="color: red"
                    >
                        {{ form.errors.medication_category_name }}
                    </span>
                </div>
                <div>
                    <button type="button" @click="closeModal">Batal</button>
                    <button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan..' : 'Simpan' }}
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
