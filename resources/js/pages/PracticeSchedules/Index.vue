<script setup>
import { ref } from 'vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
import scheduleRoute from '@/routes/practice-schedule';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell,
    TableEmpty,
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Plus,
    Pencil,
    Trash2,
    ChevronLeft,
    ChevronRight,
    X,
    Clock,
} from 'lucide-vue-next';

const props = defineProps({
    schedules: Object,
    errors: Object,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedScheduleId = ref(null);

const form = useForm({
    day: 'monday',
    session: 'morning',
    opening_hours: '08:00',
    closing_hours: '12:00',
    max_quota: '',
    status: 'open',
});

// Format nama hari ke Bahasa Indonesia TitleCase
const formatDay = (day) => {
    if (!day) return '-';
    const dayMap = {
        monday: 'Senin',
        tuesday: 'Selasa',
        wednesday: 'Rabu',
        thursday: 'Kamis',
        friday: 'Jumat',
        saturday: 'Sabtu',
        sunday: 'Minggu',
        senin: 'Senin',
        selasa: 'Selasa',
        rabu: 'Rabu',
        kamis: 'Kamis',
        jumat: 'Jumat',
        sabtu: 'Sabtu',
        minggu: 'Minggu',
    };
    return dayMap[day.toLowerCase()] || day.charAt(0).toUpperCase() + day.slice(1);
};

// Format nama sesi ke Bahasa Indonesia TitleCase
const formatSession = (session) => {
    if (!session) return '-';
    const sessionMap = {
        morning: 'Pagi',
        afternoon: 'Sore',
        evening: 'Malam',
        pagi: 'Pagi',
        sore: 'Sore',
        malam: 'Malam',
    };
    return sessionMap[session.toLowerCase()] || session.charAt(0).toUpperCase() + session.slice(1);
};

const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    form.day = 'monday';
    form.session = 'morning';
    form.opening_hours = '08:00';
    form.closing_hours = '12:00';
    form.status = 'open';
    isModalOpen.value = true;
};

const openEditModal = (schedule) => {
    isEditMode.value = true;
    selectedScheduleId.value = schedule.schedule_id ?? schedule.id;
    form.clearErrors();

    form.day = schedule.day?.toLowerCase() || 'monday';
    form.session = schedule.session?.toLowerCase() || 'morning';
    form.opening_hours = schedule.opening_hours?.substring(0, 5) || '08:00';
    form.closing_hours = schedule.closing_hours?.substring(0, 5) || '12:00';
    form.max_quota = schedule.max_quota || '';
    form.status = schedule.status || 'open';

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
    if (confirm(`Hapus jadwal hari ${formatDay(schedule.day)} sesi ${formatSession(schedule.session)}?`)) {
        router.delete(scheduleRoute.destroy.url(id));
    }
};

const formatTime = (timeStr) => {
    if (!timeStr) return '-';
    return timeStr.substring(0, 5);
};
</script>

<template>
    <Head title="Jadwal Praktik Dokter" />

    <div class="space-y-4 p-4 md:p-6 max-w-7xl">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Jadwal Praktik Dokter</h1>
                <p class="text-xs text-slate-500">Atur hari, sesi, jam buka/tutup pelayanan, kuota pasien, dan status operasional.</p>
            </div>
            <Button size="sm" class="gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white" @click="openAddModal">
                <Plus class="size-3.5" />
                Tambah Jadwal
            </Button>
        </div>

        <!-- Proporsional Clean Table -->
        <Card class="p-0 overflow-hidden border border-slate-200/80 bg-white shadow-xs rounded-lg">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-14 text-center">No</TableHead>
                        <TableHead class="w-32">Hari</TableHead>
                        <TableHead class="w-40">Sesi Pelayanan</TableHead>
                        <TableHead class="w-48">Jam Operasional</TableHead>
                        <TableHead class="w-32 text-center">Kuota Maks</TableHead>
                        <TableHead class="w-28 text-center">Status</TableHead>
                        <TableHead class="text-right pr-4 w-28">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(item, index) in schedules.data"
                        :key="item.schedule_id ?? item.id"
                    >
                        <TableCell class="text-center text-slate-400 font-mono text-[11px]">
                            {{ (schedules.current_page - 1) * schedules.per_page + (index + 1) }}
                        </TableCell>
                        <TableCell class="font-semibold text-xs text-slate-900">
                            {{ formatDay(item.day) }}
                        </TableCell>
                        <TableCell class="text-xs text-slate-700 font-medium">
                            Sesi {{ formatSession(item.session) }}
                        </TableCell>
                        <TableCell>
                            <span class="inline-flex items-center gap-1.5 font-mono text-xs text-slate-700">
                                <Clock class="size-3.5 text-slate-400" />
                                {{ formatTime(item.opening_hours) }} - {{ formatTime(item.closing_hours) }}
                            </span>
                        </TableCell>
                        <TableCell class="text-center text-slate-700 text-xs font-mono">
                            {{ item.max_quota ? `${item.max_quota} Pasien` : '-' }}
                        </TableCell>
                        <TableCell class="text-center">
                            <!-- Clean Status Dot -->
                            <span v-if="item.status === 'open'" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="size-1.5 rounded-full bg-emerald-600"></span> Buka
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                <span class="size-1.5 rounded-full bg-slate-400"></span> Tutup
                            </span>
                        </TableCell>
                        <TableCell class="text-right pr-4">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 text-slate-500 hover:text-indigo-600 border-slate-200"
                                    @click="openEditModal(item)"
                                    title="Edit"
                                >
                                    <Pencil class="size-3" />
                                </Button>
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 text-slate-400 hover:text-rose-600 hover:border-rose-200 border-slate-200"
                                    @click="deleteSchedule(item)"
                                    title="Hapus"
                                >
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="schedules.data.length === 0" :colspan="7">
                        <div class="py-10 text-center text-slate-400 text-xs">
                            Belum ada jadwal praktik dokter ditambahkan.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Pagination Bar -->
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-500">
                <span>
                    Hal <strong class="text-slate-700">{{ schedules.current_page }}</strong> dari {{ schedules.last_page }}
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!schedules.prev_page_url"
                        as-child
                    >
                        <Link v-if="schedules.prev_page_url" :href="schedules.prev_page_url">
                            <ChevronLeft class="size-3" /> Prev
                        </Link>
                        <span v-else class="opacity-40 flex items-center">
                            <ChevronLeft class="size-3" /> Prev
                        </span>
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!schedules.next_page_url"
                        as-child
                    >
                        <Link v-if="schedules.next_page_url" :href="schedules.next_page_url">
                            Next <ChevronRight class="size-3" />
                        </Link>
                        <span v-else class="opacity-40 flex items-center">
                            Next <ChevronRight class="size-3" />
                        </span>
                    </Button>
                </div>
            </div>
        </Card>

        <!-- Modal Form Tambah / Edit -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 backdrop-blur-2xs p-4">
            <div class="w-full max-w-md bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                    <h2 class="text-sm font-bold text-slate-900">
                        {{ isEditMode ? 'Edit Jadwal Praktik' : 'Tambah Jadwal Praktik' }}
                    </h2>
                    <button type="button" @click="closeModal" class="text-slate-400 hover:text-slate-600 rounded p-1">
                        <X class="size-4" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-4 space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Shadcn Select Hari -->
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Hari Praktik</label>
                            <Select :model-value="form.day" @update:model-value="(val) => form.day = val">
                                <SelectTrigger class="w-full h-8 text-xs bg-white border-slate-200">
                                    <SelectValue placeholder="Pilih Hari" />
                                </SelectTrigger>
                                <SelectContent class="bg-white border-slate-200">
                                    <SelectItem value="monday" class="text-xs">Senin</SelectItem>
                                    <SelectItem value="tuesday" class="text-xs">Selasa</SelectItem>
                                    <SelectItem value="wednesday" class="text-xs">Rabu</SelectItem>
                                    <SelectItem value="thursday" class="text-xs">Kamis</SelectItem>
                                    <SelectItem value="friday" class="text-xs">Jumat</SelectItem>
                                    <SelectItem value="saturday" class="text-xs">Sabtu</SelectItem>
                                    <SelectItem value="sunday" class="text-xs">Minggu</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Shadcn Select Sesi -->
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Sesi Pelayanan</label>
                            <Select :model-value="form.session" @update:model-value="(val) => form.session = val">
                                <SelectTrigger class="w-full h-8 text-xs bg-white border-slate-200">
                                    <SelectValue placeholder="Pilih Sesi" />
                                </SelectTrigger>
                                <SelectContent class="bg-white border-slate-200">
                                    <SelectItem value="morning" class="text-xs">Pagi</SelectItem>
                                    <SelectItem value="afternoon" class="text-xs">Sore</SelectItem>
                                    <SelectItem value="evening" class="text-xs">Malam</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Shadcn Time Picker Controls -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Jam Buka</label>
                            <div class="relative flex items-center">
                                <input
                                    type="time"
                                    v-model="form.opening_hours"
                                    class="w-full h-8 rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs text-slate-800 font-mono shadow-2xs focus:border-indigo-500 focus:outline-none cursor-pointer"
                                />
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Jam Tutup</label>
                            <div class="relative flex items-center">
                                <input
                                    type="time"
                                    v-model="form.closing_hours"
                                    class="w-full h-8 rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs text-slate-800 font-mono shadow-2xs focus:border-indigo-500 focus:outline-none cursor-pointer"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Kuota Maks Pasien</label>
                            <input
                                type="number"
                                v-model="form.max_quota"
                                placeholder="Contoh: 30"
                                min="1"
                                class="w-full h-8 rounded-md border border-slate-200 px-3 py-1 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none font-mono"
                            />
                        </div>

                        <!-- Shadcn Select Status -->
                        <div class="space-y-1">
                            <label class="font-semibold text-slate-700">Status Operasional</label>
                            <Select :model-value="form.status" @update:model-value="(val) => form.status = val">
                                <SelectTrigger class="w-full h-8 text-xs bg-white border-slate-200">
                                    <SelectValue placeholder="Pilih Status" />
                                </SelectTrigger>
                                <SelectContent class="bg-white border-slate-200">
                                    <SelectItem value="open" class="text-xs">Buka</SelectItem>
                                    <SelectItem value="closed" class="text-xs">Tutup</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <Button type="button" variant="outline" size="sm" @click="closeModal">Batal</Button>
                        <Button type="submit" size="sm" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Jadwal' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
