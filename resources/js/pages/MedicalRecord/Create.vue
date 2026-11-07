<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    queue: Object,
    patient: Object,
    medications: Array,
    errors: Object, // Menangkap error transaksi dari controller
});

const form = useForm({
    patient_id: props.patient.patient_id,
    main_complaint: '',
    blood_pressure: '',
    temperature: '',
    weight: '',
    diagnosis: '',
    notes_on_treatment: '',
    prescriptions: [] // Array kosong untuk menampung obat-obatan
});

// Fungsi untuk menambah baris resep obat baru
const addPrescription = () => {
    form.prescriptions.push({
        medication_id: '',
        qty: 1,
        dosage: ''
    });
};

// Fungsi untuk menghapus baris resep
const removePrescription = (index) => {
    form.prescriptions.splice(index, 1);
};

const submit = () => {
    if (confirm('Simpan hasil pemeriksaan? Status pasien akan menjadi Selesai.')) {
        form.post(`/medical-records/${props.queue.queue_id}`);
    }
};
</script>

<template>
    <div style="font-family: sans-serif; padding: 20px; max-width: 900px; margin: 0 auto;">
        <h1>Pemeriksaan Medis</h1>

        <div style="background-color: #f3f4f6; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            <p><strong>Nama Pasien:</strong> {{ props.patient.patient_name }}</p>
            <p><strong>No. Antrean:</strong> {{ props.queue.queue_number }} (Sesi: {{ props.queue.session }})</p>
        </div>

        <!-- Alert Error Database/Transaksi (misal: stok habis) -->
        <div v-if="props.errors.error_transaction" style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 20px;">
            <strong>Gagal Menyimpan!</strong> {{ props.errors.error_transaction }}
        </div>

        <form @submit.prevent="submit">
            <fieldset style="margin-bottom: 20px; padding: 15px;">
                <legend><strong>1. Hasil Diagnosa & Pemeriksaan Fisik</strong></legend>

                <div style="margin-bottom: 10px;">
                    <label>Keluhan Utama (Wajib):</label><br>
                    <textarea v-model="form.main_complaint" required style="width: 100%;" rows="3"></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <div style="flex: 1">
                        <label>Tensi Darah (mmHg):</label>
                        <input type="text" v-model="form.blood_pressure" placeholder="Contoh: 120/80" style="width: 100%;">
                    </div>
                    <div style="flex: 1">
                        <label>Suhu Badan (°C):</label>
                        <input type="number" step="0.1" v-model="form.temperature" style="width: 100%;">
                    </div>
                    <div style="flex: 1">
                        <label>Berat Badan (Kg):</label>
                        <input type="number" step="0.1" v-model="form.weight" style="width: 100%;">
                    </div>
                </div>

                <div style="margin-bottom: 10px;">
                    <label>Diagnosa Medis (Wajib):</label><br>
                    <textarea v-model="form.diagnosis" required style="width: 100%;" rows="3"></textarea>
                </div>

                <div>
                    <label>Catatan Tindakan Tambahan:</label><br>
                    <textarea v-model="form.notes_on_treatment" style="width: 100%;" rows="2"></textarea>
                </div>
            </fieldset>

            <fieldset style="margin-bottom: 20px; padding: 15px;">
                <legend><strong>2. Resep Obat</strong></legend>

                <button type="button" @click="addPrescription" style="margin-bottom: 15px; cursor: pointer;">
                    + Tambah Resep Obat
                </button>

                <table v-if="form.prescriptions.length > 0" border="1" cellpadding="8" cellspacing="0" style="width: 100%; text-align: left;">
                    <thead>
                        <tr>
                            <th>Pilih Obat</th>
                            <th>Dosis / Aturan Pakai</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.prescriptions" :key="index">
                            <td>
                                <select v-model="item.medication_id" required style="width: 100%;">
                                    <option value="" disabled>-- Pilih Obat --</option>
                                    <option v-for="med in props.medications" :key="med.medication_id" :value="med.medication_id">
                                        {{ med.medication_name }} (Stok: {{ med.stock }}) - Rp {{ med.unit_price }}
                                    </option>
                                </select>
                            </td>
                            <td>
                                <input type="text" v-model="item.dosage" placeholder="Contoh: 3x Sehari 1 Tablet" required style="width: 100%;">
                            </td>
                            <td>
                                <input type="number" v-model="item.qty" min="1" required style="width: 60px;">
                            </td>
                            <td>
                                <button type="button" @click="removePrescription(index)" style="color: red; cursor: pointer;">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else style="color: gray;">Tidak ada obat yang diresepkan.</p>
            </fieldset>

            <div style="display: flex; gap: 10px;">
                <button type="submit" :disabled="form.processing" style="padding: 10px 20px; font-weight: bold; cursor: pointer;">
                    Simpan & Selesai
                </button>
                <Link href="/queue-management" style="padding: 10px 20px; text-decoration: none; color: black; border: 1px solid black; display: inline-block;">
                    Batal
                </Link>
            </div>
        </form>
    </div>
</template>
