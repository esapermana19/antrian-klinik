<script setup>
import { ref } from 'vue';
import { useForm, router, Link, Head, usePage } from '@inertiajs/vue3';
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
    Plus,
    Pencil,
    Trash2,
    Search,
    KeyRound,
    ChevronLeft,
    ChevronRight,
    X,
    Shield,
    User,
    CheckCircle2,
    XCircle,
} from 'lucide-vue-next';
import MotionModal from '@/components/MotionModal.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';

const props = defineProps({
    users: Object,
    filters: Object,
    errors: Object,
});

const page = usePage();
const currentAuthId =
    page.props.auth?.user?.id || page.props.auth?.user?.user_id;

const searchQuery = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');
let searchTimeout = null;

const handleFilter = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/users',
            {
                search: searchQuery.value || undefined,
                role: roleFilter.value || undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
};

// Modal Form Tambah/Edit
const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedUser = ref(null);

const form = useForm({
    username: '',
    email: '',
    role: 'user',
    password: '',
});

// Modal Reset Password
const isResetModalOpen = ref(false);
const resetForm = useForm({
    new_password: '',
});

// Modal Delete
const isDeleteModalOpen = ref(false);
const userToDelete = ref(null);
const isDeleting = ref(false);

const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (u) => {
    isEditMode.value = true;
    selectedUser.value = u;
    form.clearErrors();
    form.username = u.username;
    form.email = u.email;
    form.role = u.role;
    form.password = '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditMode.value && selectedUser.value) {
        form.put(`/users/${selectedUser.value.user_id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/users', {
            onSuccess: () => closeModal(),
        });
    }
};

const openResetModal = (u) => {
    selectedUser.value = u;
    resetForm.reset();
    resetForm.clearErrors();
    isResetModalOpen.value = true;
};

const submitResetPassword = () => {
    if (!selectedUser.value) return;
    resetForm.patch(`/users/${selectedUser.value.user_id}/reset-password`, {
        onSuccess: () => {
            isResetModalOpen.value = false;
            resetForm.reset();
        },
    });
};

const confirmDeleteUser = (u) => {
    userToDelete.value = u;
    isDeleteModalOpen.value = true;
};

const handleDeleteUser = () => {
    if (!userToDelete.value) return;
    isDeleting.value = true;
    router.delete(`/users/${userToDelete.value.user_id}`, {
        onFinish: () => {
            isDeleting.value = false;
            isDeleteModalOpen.value = false;
            userToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <div class="max-w-7xl space-y-4 p-4 md:p-6">
        <!-- Top Action / Header Area -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">
                    Manajemen Pengguna
                </h1>
                <p class="text-xs text-slate-500">
                    Kelola dan pantau seluruh data staf, dokter, dan pasien
                    klinik.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Filter Role -->
                <select
                    v-model="roleFilter"
                    @change="handleFilter"
                    class="h-8 cursor-pointer rounded-lg border border-slate-200/80 bg-white/80 px-2.5 text-xs text-slate-700 shadow-2xs transition focus:border-indigo-500 focus:bg-white focus:outline-none"
                >
                    <option value="">Semua Role</option>
                    <option value="administrator">Administrator</option>
                    <option value="user">User Pasien</option>
                </select>

                <!-- Search Input Pengguna -->
                <div class="relative w-64">
                    <Search
                        class="absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="handleFilter"
                        placeholder="Cari username, email, kode..."
                        class="h-8 w-full rounded-lg border border-slate-200/80 bg-white/80 pr-3 pl-8 text-xs shadow-2xs transition focus:border-indigo-500 focus:bg-white focus:outline-none"
                    />
                </div>

                <Button
                    size="sm"
                    class="gap-1.5 bg-indigo-600 font-medium text-white shadow-xs hover:bg-indigo-700"
                    @click="openAddModal"
                >
                    <Plus class="size-3.5" />
                    Tambah Pengguna
                </Button>
            </div>
        </div>

        <!-- Main Card & Clean Table (Shadcn Style) -->
        <Card
            class="overflow-hidden rounded-xl border border-slate-200/80 bg-white p-0 shadow-xs"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12 text-center">No</TableHead>
                        <TableHead class="w-32">Kode User</TableHead>
                        <TableHead>Pengguna</TableHead>
                        <TableHead class="w-36">Role</TableHead>
                        <TableHead class="w-36">Verifikasi Email</TableHead>
                        <TableHead class="w-36">Pasien Terhubung</TableHead>
                        <TableHead class="w-32 pr-4 text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(u, index) in users.data" :key="u.user_id">
                        <!-- No -->
                        <TableCell
                            class="text-center font-mono text-[11px] text-slate-400"
                        >
                            {{
                                (users.current_page - 1) * users.per_page +
                                (index + 1)
                            }}
                        </TableCell>

                        <!-- Kode User -->
                        <TableCell class="font-mono text-xs text-slate-700">
                            {{ u.user_code || '-' }}
                        </TableCell>

                        <!-- Pengguna (Nama & Email) -->
                        <TableCell>
                            <div class="text-xs font-medium text-slate-900">
                                {{ u.username }}
                            </div>
                            <div class="font-mono text-[11px] text-slate-400">
                                {{ u.email }}
                            </div>
                        </TableCell>

                        <!-- Role -->
                        <TableCell>
                            <span
                                v-if="u.role === 'administrator'"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-700"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-indigo-600"
                                ></span>
                                Administrator
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-slate-400"
                                ></span>
                                User Pasien
                            </span>
                        </TableCell>

                        <!-- Verifikasi Email -->
                        <TableCell>
                            <span
                                v-if="u.email_verified_at"
                                class="inline-flex items-center gap-1 text-xs text-emerald-600"
                            >
                                <CheckCircle2 class="size-3.5" /> Terverifikasi
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 text-xs text-amber-600"
                            >
                                <XCircle class="size-3.5" /> Belum
                            </span>
                        </TableCell>

                        <!-- Pasien Terhubung -->
                        <TableCell class="text-xs text-slate-600">
                            {{ u.patients_count ?? 0 }} pasien
                        </TableCell>

                        <!-- Aksi -->
                        <TableCell class="pr-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 border-slate-200 text-slate-500 hover:text-amber-600"
                                    @click="openResetModal(u)"
                                    title="Reset Password"
                                >
                                    <KeyRound class="size-3" />
                                </Button>
                                <Button
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 border-slate-200 text-slate-500 hover:text-indigo-600"
                                    @click="openEditModal(u)"
                                    title="Edit"
                                >
                                    <Pencil class="size-3" />
                                </Button>
                                <Button
                                    v-if="u.user_id !== currentAuthId"
                                    size="icon-sm"
                                    variant="outline"
                                    class="size-7 border-slate-200 text-slate-400 hover:border-rose-200 hover:text-rose-600"
                                    @click="confirmDeleteUser(u)"
                                    title="Hapus"
                                >
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="users.data.length === 0" :colspan="7">
                        <div class="py-10 text-center text-xs text-slate-400">
                            Tidak ada pengguna ditemukan.
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>

            <!-- Pagination Bar -->
            <div
                class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-4 py-2.5 text-xs text-slate-500"
            >
                <span>
                    Hal
                    <strong class="text-slate-700">{{
                        users.current_page
                    }}</strong>
                    dari {{ users.last_page }} (Total: {{ users.total }})
                </span>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!users.prev_page_url"
                        as-child
                    >
                        <Link
                            v-if="users.prev_page_url"
                            :href="users.prev_page_url"
                        >
                            <ChevronLeft class="size-3" />
                            Prev
                        </Link>
                        <span v-else class="flex items-center opacity-40">
                            <ChevronLeft class="size-3" />
                            Prev
                        </span>
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        class="h-7 px-2 text-xs"
                        :disabled="!users.next_page_url"
                        as-child
                    >
                        <Link
                            v-if="users.next_page_url"
                            :href="users.next_page_url"
                        >
                            Next
                            <ChevronRight class="size-3" />
                        </Link>
                        <span v-else class="flex items-center opacity-40">
                            Next
                            <ChevronRight class="size-3" />
                        </span>
                    </Button>
                </div>
            </div>
        </Card>

        <!-- Modal Form Tambah / Edit (MotionModal Style Konsisten Pasien) -->
        <MotionModal
            :show="isModalOpen"
            max-width="max-w-md"
            @close="closeModal"
        >
            <div
                class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3"
            >
                <h2 class="text-sm font-bold text-slate-900">
                    {{
                        isEditMode
                            ? 'Edit Data Pengguna'
                            : 'Tambah Pengguna Baru'
                    }}
                </h2>
                <button
                    type="button"
                    @click="closeModal"
                    class="rounded p-1 text-slate-400 hover:text-slate-600"
                >
                    <X class="size-4" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-3 p-4 text-xs">
                <div class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Username <span class="text-rose-500">*</span></label
                    >
                    <input
                        type="text"
                        v-model="form.username"
                        placeholder="Contoh: staf_klinik"
                        class="h-8 w-full rounded-lg border border-slate-200 px-2.5 text-xs focus:border-indigo-500 focus:outline-none"
                        required
                    />
                    <p
                        v-if="form.errors.username"
                        class="mt-0.5 text-[11px] text-rose-500"
                    >
                        {{ form.errors.username }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Alamat Email
                        <span class="text-rose-500">*</span></label
                    >
                    <input
                        type="email"
                        v-model="form.email"
                        placeholder="Contoh: staf@klinik.test"
                        class="h-8 w-full rounded-lg border border-slate-200 px-2.5 text-xs focus:border-indigo-500 focus:outline-none"
                        required
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-0.5 text-[11px] text-rose-500"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Role Akses <span class="text-rose-500">*</span></label
                    >
                    <select
                        v-model="form.role"
                        class="h-8 w-full cursor-pointer rounded-lg border border-slate-200 px-2.5 text-xs focus:border-indigo-500 focus:outline-none"
                    >
                        <option value="user">
                            User Pasien (Akses Antrean Pasien)
                        </option>
                        <option value="administrator">
                            Administrator (Akses Penuh Kelola Klinik)
                        </option>
                    </select>
                    <p
                        v-if="form.errors.role"
                        class="mt-0.5 text-[11px] text-rose-500"
                    >
                        {{ form.errors.role }}
                    </p>
                </div>

                <div v-if="!isEditMode" class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Kata Sandi <span class="text-rose-500">*</span></label
                    >
                    <input
                        type="password"
                        v-model="form.password"
                        placeholder="Minimal 8 karakter"
                        class="h-8 w-full rounded-lg border border-slate-200 px-2.5 text-xs focus:border-indigo-500 focus:outline-none"
                        required
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-0.5 text-[11px] text-rose-500"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <div
                    class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3"
                >
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="h-8 border-slate-200 text-xs"
                        @click="closeModal"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        size="sm"
                        class="h-8 bg-indigo-600 text-xs font-medium text-white hover:bg-indigo-700"
                        :disabled="form.processing"
                    >
                        {{
                            isEditMode ? 'Simpan Perubahan' : 'Tambah Pengguna'
                        }}
                    </Button>
                </div>
            </form>
        </MotionModal>

        <!-- Modal Reset Password -->
        <MotionModal
            :show="isResetModalOpen"
            max-width="max-w-sm"
            @close="isResetModalOpen = false"
        >
            <div
                class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3"
            >
                <h2 class="text-sm font-bold text-slate-900">Reset Password</h2>
                <button
                    type="button"
                    @click="isResetModalOpen = false"
                    class="rounded p-1 text-slate-400 hover:text-slate-600"
                >
                    <X class="size-4" />
                </button>
            </div>

            <form
                @submit.prevent="submitResetPassword"
                class="space-y-3 p-4 text-xs"
            >
                <p class="text-slate-500">
                    Ketik kata sandi baru untuk akun
                    <strong>{{ selectedUser?.username }}</strong
                    >.
                </p>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700"
                        >Password Baru
                        <span class="text-rose-500">*</span></label
                    >
                    <input
                        type="password"
                        v-model="resetForm.new_password"
                        placeholder="Minimal 8 karakter"
                        class="h-8 w-full rounded-lg border border-slate-200 px-2.5 text-xs focus:border-indigo-500 focus:outline-none"
                        required
                    />
                    <p
                        v-if="resetForm.errors.new_password"
                        class="mt-0.5 text-[11px] text-rose-500"
                    >
                        {{ resetForm.errors.new_password }}
                    </p>
                </div>

                <div
                    class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3"
                >
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="h-8 border-slate-200 text-xs"
                        @click="isResetModalOpen = false"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        size="sm"
                        class="h-8 bg-amber-600 text-xs font-medium text-white hover:bg-amber-700"
                        :disabled="resetForm.processing"
                    >
                        Simpan Password
                    </Button>
                </div>
            </form>
        </MotionModal>

        <!-- Modal Konfirmasi Hapus -->
        <ConfirmDeleteModal
            :show="isDeleteModalOpen"
            title="Hapus Pengguna?"
            :message="`Apakah Anda yakin ingin menghapus akun ${userToDelete?.username}? Seluruh data pasien yang terhubung akan ikut terhapus.`"
            @close="isDeleteModalOpen = false"
            @confirm="handleDeleteUser"
        />
    </div>
</template>
