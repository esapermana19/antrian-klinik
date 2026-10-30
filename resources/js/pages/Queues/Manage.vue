<script setup>
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    todayQueues: Object,
});

const updateStatus = (queueId, newStatus) => {
    if (confirm(`Ubah status antrean menjadi ${newStatus}?`)) {
        router.patch(`/queue-management/${queueId}/status`, {
            queue_status: newStatus,
        }, {
            preserveScroll: true,
        });
    }
};

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
        <h1>Kelola Antrian</h1>
        <p>Daftar antrean operasional hari ini.</p>

        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>No. Antrean</th>
                    <th>Nama Pasien</th>
                    <th>Sesi</th>
                    <th>Estimasi Jam</th>
                    <th>Status Saat Ini</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="q in todayQueues.data" :key="q.queue_id">
                    <td><strong>{{ q.queue_number }}</strong></td>
                    <td>{{ q.patient?.patient_name }}</td>
                    <td>{{ formatSession(q.session) }}</td>
                    <td>{{ q.estimate_time }}</td>
                    <td><strong>{{ q.queue_status.toUpperCase() }}</strong></td>
                    <td>
                        <button v-if="q.queue_status === 'waiting'" type="button"
                            @click="updateStatus(q.queue_id, 'called')">
                            Panggil Pasien
                        </button>

                        <button v-if="q.queue_status === 'called'" type="button"
                            @click="updateStatus(q.queue_id, 'checking')">
                            Masuk Ruang Periksa
                        </button>
                        <button v-if="q.queue_status === 'checking'" type="button"
                            @click="updateStatus(q.queue_id, 'completed')">
                            Selesai Periksa
                        </button>
                        <button v-if="['waiting', 'called'].includes(q.queue_status)" type="button"
                            @click="updateStatus(q.queue_id, 'cancel')" style="margin-left: 5px;">
                            Batalkan
                        </button>
                    </td>
                </tr>
                <tr v-if="!todayQueues.data || todayQueues.data.length === 0">
                    <td colspan="6">Tidak ada antrian untuk hari ini</td>
                </tr>
            </tbody>
        </table>

        <div>
            <Link v-if="todayQueues.prev_page_url" :href="todayQueues.prev_page_url">
            Previous
            </Link>
            <button v-else disabled>Previous</button>

            <span>
                Halaman {{ todayQueues.current_page }} dari {{ todayQueues.last_page }}
            </span>

            <Link v-if="todayQueues.next_page_url" :href="todayQueues.next_page_url">
            Next
            </Link>
            <button v-else disabled>Next</button>
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
