<script setup>
import myQueue from '@/routes/my-queue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    myQueues: Array,
});

const formatSession = (session) => {
    const sessionName = {
        morning: 'Pagi',
        afternoon: 'Sore',
    };
    return sessionName[session] || session;
}

</script>

<template>
    <div>
        <h1>Tiket Antrian Saya</h1>
        <p>Daftar Antrean yang pernah kamu daftarkan.
            <Link href="/queue">+ Ambil Antrean Baru</Link>
        </p>

        <div v-if="props.myQueues && props.myQueues.length > 0">
            <div v-for="item in props.myQueues" :key="item.queue_id"
                style="border: 1px solid black; padding: 12px; margin-bottom: 12px; width: 350px;">
                <h2>{{ item.queue_number }}</h2>
                <p><strong>Nama Pasien:</strong> {{ item.patient?.patient_name }}</p>
                <p><strong>NIK:</strong> {{ item.patient?.nik }}</p>
                <p><strong>Tanggal Periksa:</strong> {{ item.patient?.queue_date }}</p>
                <p><strong>Sesi / Estimasi Jam:</strong> {{ formatSession(item.session) }} ({{ item.estimate_time }})</p>
                <p><strong>Status</strong>
                <span>{{ item.queue_status.toUpperCase() ?? '-' }}</span></p>
            </div>
        </div>

        <div v-else>
            <p>Kamu belum memiliki riwayat atau tiekt antrean aktif.</p>
        </div>
    </div>
</template>
