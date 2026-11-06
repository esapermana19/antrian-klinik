<script setup>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    currentQueue: Object,
    nextQueues: Array,
});

const countdown = ref('');

const updateCountdown = () => {
    if (!props.currentQueue?.estimate_time) {
        countdown.value = '-';
        return;
    }

    let targetDate;

    if (
        props.currentQueue.queue_status === 'checking' ||
        props.currentQueue.queue_status === 'called'
    ) {
        targetDate = new Date(props.currentQueue.updated_at);
        targetDate.setMinutes(targetDate.getMinutes() + 15);
    } else {
        const today = new Date().toISOString().split('T')[0];
        targetDate = new Date(`${today}T${props.currentQueue.estimate_time}`);
    }

    const now = new Date();
    const diff = targetDate.getTime() - now.getTime();

    if (diff <= 0) {
        countdown.value = 'Waktu Habis / Sedang Berlangsung';
        return;
    }

    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    countdown.value = `${minutes} Menit ${seconds} Detik`;
};

const formatStatus = (status) => {
    const statusName = {
        called: 'DIPANGGIL',
        checking: 'DIPERIKSA',
        completed: 'SELESAI'
    };
    return statusName[status.toLowerCase()] || status.toUpperCase();
};

onMounted(() => {
    updateCountdown();
    setInterval(updateCountdown, 1000);
    setInterval(() => {
        router.reload({ only: ['currentQueue', 'nextQueues'] });
    }, 3000);
});
</script>

<template>
    <div style="padding: 20px; font-family: sans-serif;">
        <h1 style="text-align: center; margin-bottom: 30px;">Layar Panggilan Antrean</h1>

        <!-- Container Utama (Membagi Kiri dan Kanan) -->
        <div style="display: flex; gap: 20px; align-items: flex-start;">

            <!-- KIRI: ANTREAN SAAT INI -->
            <div style="flex: 1; border: 2px solid black; padding: 40px; text-align: center; min-height: 300px;">
                <h2 style="margin-bottom: 10px;">Antrian Saat Ini</h2>

                <div v-if="props.currentQueue">
                    <h1 style="font-size: 3rem; margin: 10px 0; color: #b22222;">
                        {{ props.currentQueue.queue_number }}
                    </h1>
                    <h3 style="margin: 5px 0;">
                        {{ props.currentQueue.patient?.patient_name }}
                    </h3>
                    <p style="margin-top: 20px; font-size: 1.2rem; font-weight: bold;">
                        Status: {{ formatStatus(props.currentQueue?.queue_status) }}
                    </p>
                    <p style="margin-top: 10px; color: gray;">
                        Estimasi: {{ countdown }}
                    </p>
                </div>

                <div v-else style="margin-top: 50px; color: gray;">
                    Selesai / Belum ada antrean yang dipanggil.
                </div>
            </div>

            <!-- KANAN: ANTREAN SELANJUTNYA -->
            <div style="flex: 2; display: flex; flex-direction: column; gap: 15px;">

                <div
                    v-for="(queue, index) in props.nextQueues"
                    :key="queue.queue_id"
                    style="border: 1px solid black; padding: 20px; text-align: center;"
                >
                    <h4 style="margin: 0; font-weight: normal;">Antrian Selanjutnya {{ index + 1 }}</h4>
                    <h2 style="margin: 5px 0; font-size: 2rem;">{{ queue.queue_number }}</h2>
                    <p style="margin: 0;">{{ queue.patient?.patient_name }}</p>
                </div>

                <div
                    v-if="!props.nextQueues || props.nextQueues.length === 0"
                    style="border: 1px solid black; padding: 20px; text-align: center; color: gray;"
                >
                    Tidak ada antrean selanjutnya.
                </div>

            </div>
        </div>
    </div>
</template>
