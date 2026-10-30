<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import medicationRoute from '@/routes/medication';

const props = defineProps({
    medications: Object,
    errors: Object,
    medication_categories: Array,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMedicationId = ref(null);

const form = useForm({
    medication_category_id: '',
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
    form.medication_category_id =
        medication.medication_category_id ??
        medication.medication_category?.medication_category_id ??
        '';
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
        form.put(medicationRoute.update.url(selectedMedicationId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(medicationRoute.store.url(), {
            onSuccess: () => closeModal(),
            onError: (errors) => console.log('Error validasi tambah:', errors),
        });
    }
};

const deleteMedication = (medication) => {
    const id = medication.medication_id ?? medication.id;
    if (confirm(`Hapus data obat?`)) {
        router.delete(medicationRoute.destroy.url(id));
    }
};
</script>

<template>
    <div>
        <h1>Master Data Obat</h1>
        <p>Kelola ketersediaan obat di klinik.</p>
        <button type="button" @click="openAddModal">+ Tambah Obat</button>
        <hr />

        <table cellpadding="20" cellspacing="0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori Obat</th>
                    <th>Kode Obat</th>
                    <th>Nama Obat</th>
                    <th>Stok</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(item, index) in medications.data"
                    :key="item.medication_id ?? item.id"
                >
                    <td>
                        {{
                            (medications.current_page - 1) *
                                medications.per_page +
                            (index + 1)
                        }}
                    </td>
                    <td>
                        {{
                            item.medicationCategory?.medication_category_name ??
                            item.medication_category
                                ?.medication_category_name ??
                            '-'
                        }}
                    </td>
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

                <div>
            <Link v-if="medications.prev_page_url" :href="medications.prev_page_url">
                Previous
            </Link>
            <button v-else disabled>Previous</button>

            <span>
                Halaman {{ medications.current_page }} dari {{ medications.last_page }}
            </span>

            <Link v-if="medications.next_page_url" :href="medications.next_page_url">
                Next
            </Link>
            <button v-else disabled>Next</button>
        </div>

        <div v-if="isModalOpen">
            <hr />
            <h2>
                {{ isEditMode ? 'Edit Data Obat' : 'Tambah Data Obat' }}
            </h2>

            <form @submit.prevent="submitForm">
                <div>
                    <label>Kategori: </label>
                    <select v-model="form.medication_category_id">
                        <option value="">--Pilih Kategori--</option>
                        <option
                            v-for="cat in medication_categories"
                            :key="cat.medication_category_id"
                            :value="cat.medication_category_id"
                        >
                            {{ cat.medication_category_name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label>Nama Obat: </label>
                    <input type="text" v-model="form.medication_name" />
                    <span v-if="form.errors.medication_name" style="color: red">
                        {{ form.errors.medication_name }}
                    </span>
                </div>

                <div>
                    <label>Stok: </label>
                    <input type="number" v-model="form.stock" min="0" />
                    <span v-if="form.errors.stock" style="color: red">
                        {{ form.errors.stock }}
                    </span>
                </div>

                <div>
                    <label>Unit Price: </label>
                    <input type="number" v-model="form.unit_price" />
                    <span v-if="form.errors.unit_price" style="color: red">
                        {{ form.errors.unit_price }}
                    </span>
                </div>

                <div>
                    <button type="button" @click="closeModal">Batal</button>
                    <button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan..' : 'SImpan' }}
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
