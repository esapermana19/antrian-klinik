# Panduan Lengkap: Memahami `<script setup>` Inertia Vue & Pola CRUD Halaman Index

Dokumen ini dibuat sebagai referensi praktis agar Anda dapat memahami alur kerja frontend–backend pada halaman [Index.vue](file:///d:/Work/Antrian%20Klinik/antrian-klinik/resources/js/pages/PracticeSchedules/Index.vue), serta dapat menerapkannya secara mandiri saat membuat modul/fitur baru (seperti Dokter, Poliklinik, Pasien, Obat, dll).

---

## 1. Bedah Anatomi `<script setup>` (Bagian per Bagian)

Di Vue 3 Composition API dengan Inertia.js, tag `<script setup>` di halaman index dibagi menjadi **6 blok fungsional utama**:

### A. Import Dependencies

```javascript
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
```

| Modul     | Asal       | Fungsi                                                                                                                                                                                |
| :-------- | :--------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `ref`     | Vue Core   | Membuat variabel primitif (boolean, string, number) menjadi **reaktif**. Nilai dibaca/diubah dengan `.value`.                                                                         |
| `useForm` | Inertia.js | Wrapper reaktif khusus form. Otomatis mengurus data input, status loading (`form.processing`), penanganan error validasi (`form.errors`), serta method HTTP (`post`, `put`, `patch`). |
| `router`  | Inertia.js | Pengendali navigasi SPA. Digunakan untuk aksi HTTP yang tidak membungkus data form besar, contohnya tombol **Hapus** (`router.delete()`).                                             |

---

### B. Menerima Data dari Controller (`defineProps`)

```javascript
const props = defineProps({
    schedules: Object,
    errors: Object,
});
```

- **Sumber Data**: Dikirim dari controller Laravel melalui `Inertia::render('PracticeSchedules/Index', ['schedules' => $schedules])`.
- **Penggunaan di Template**: Objek paginasi Laravel memiliki properti `.data`. Di template dipanggil dengan:
    ```html
    <tr v-for="item in schedules.data" :key="item.id">
        ...
    </tr>
    ```

---

### C. State Kontrol Modal / UI

```javascript
const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedScheduleId = ref(null);
```

- **`isModalOpen`**: Mengatur visibilitas form modal (`v-if="isModalOpen"`).
- **`isEditMode`**: Flag penanda apakah user sedang menambah data baru (`false`) atau mengedit data lama (`true`). Mengubah teks judul modal dan aksi submit.
- **`selectedScheduleId`**: Menyimpan ID record yang sedang diedit agar saat submit sistem tahu target URL update-nya (`/practice-schedule/{id}`).

---

### D. Inisialisasi Objek Form (`useForm`)

```javascript
const form = useForm({
    day: '',
    session: '',
    opening_hours: '',
    closing_hours: '',
    max_quota: '',
    status: 'open',
});
```

- Menampung seluruh field input yang akan dikirim ke backend.
- Di template diikat dua arah menggunakan `v-model`:
    ```html
    <select v-model="form.day">
        ...
    </select>
    <input v-model="form.max_quota" />
    ```
- Jika validasi backend gagal, pesan error otomatis dapat dibaca di template melalui `form.errors.nama_field`:
    ```html
    <span v-if="form.errors.day" class="text-red-500"
        >{{ form.errors.day }}</span
    >
    ```

---

### E. Handler Buka & Tutup Modal

```javascript
// 1. Tambah Data Baru
const openAddModal = () => {
    isEditMode.value = false;
    form.reset(); // Mengosongkan field input form
    form.clearErrors(); // Membersihkan error validasi merah sebelumnya
    isModalOpen.value = true;
};

// 2. Edit Data
const openEditModal = (schedule) => {
    isEditMode.value = true;
    selectedScheduleId.value = schedule.schedule_id ?? schedule.id;
    form.clearErrors();

    // Mapping nilai dari baris tabel ke dalam field form
    form.day = schedule.day;
    form.session = schedule.session;
    form.opening_hours = schedule.opening_hours.substring(0, 5);
    form.closing_hours = schedule.closing_hours.substring(0, 5);
    form.max_quota = schedule.max_quota;
    form.status = schedule.status;

    isModalOpen.value = true;
};

// 3. Tutup Modal
const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};
```

---

### F. Eksekusi HTTP Request (`submitForm` & `deleteSchedule`)

```javascript
const submitForm = () => {
    if (isEditMode.value) {
        // Mode Edit -> HTTP PUT
        form.put(`/practice-schedule/${selectedScheduleId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        // Mode Tambah -> HTTP POST
        form.post('/practice-schedule', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSchedule = (schedule) => {
    const id = schedule.schedule_id ?? schedule.id;
    if (
        confirm(`Hapus jadwal hari ${schedule.day} sesi ${schedule.session}?`)
    ) {
        // Hapus Data -> HTTP DELETE
        router.delete(`/practice-schedule/${id}`);
    }
};
```

- **Kelebihan Inertia**: Tidak memerlukan reload satu halaman penuh (page reload). Ketika backend selesai memproses request dan memanggil `redirect()->back()`, Inertia secara otomatis memperbarui data `props.schedules` di browser.

---

## 2. Hubungan dengan File Lain di Laravel

Berikut alur komunikasi data antara file Vue dan arsitektur Laravel:

```
[ Browser / Vue ]                                   [ Backend Laravel ]
┌───────────────────────────┐                       ┌──────────────────────────────────────────────┐
│ Index.vue (<script setup>)│                       │ routes/web.php                               │
│                           │  HTTP GET /schedule   │ Route::resource('practice-schedule', ...)    │
│  - props.schedules ◄──────┼───────────────────────┼───► PracticeScheduleController@index         │
│                           │                       │                                              │
│  - form.post('/...') ─────┼──HTTP POST /schedule──┼───► PracticeScheduleController@store         │
│                           │                       │       ├── StorePracticeScheduleRequest (Rule)│
│  - form.put('/.../{id}') ─┼──HTTP PUT /sched/{id}─┼───► PracticeScheduleController@update        │
│                           │                       │       └── Model::save()                      │
│  - router.delete(...) ────┼──HTTP DELETE /.../{id}┼───► PracticeScheduleController@destroy       │
└───────────────────────────┘                       └──────────────────────────────────────────────┘
```

1. **[routes/web.php](file:///d:/Work/Antrian%20Klinik/antrian-klinik/routes/web.php)**:
   Mendefinisikan endpoint URL yang dituju oleh `form.post`, `form.put`, dan `router.delete`.
2. **[PracticeScheduleController.php](file:///d:/Work/Antrian%20Klinik/antrian-klinik/app/Http/Controllers/PracticeScheduleController.php)**:
    - `index()`: Mengambil data database (dengan pagination/ordering) lalu mengirimkannya sebagai prop ke Vue via `Inertia::render()`.
    - `store()` / `update()`: Menerima data dari frontend, melakukan validasi Form Request, membuat/mengubah data di DB, lalu `to_route(...)` atau `back()`.
    - `destroy()`: Menghapus record dan me-redirect kembali ke index.
3. **Form Request (`StorePracticeScheduleRequest.php`)**:
   Jika validasi gagal, Laravel langsung melempar error code 422 kembali ke Inertia, yang otomatis terikat ke properti `form.errors`.

---

## 3. Template Blueprint: Cara Ngoding Fitur Lain Secara Mandiri

Gunakan template di bawah ini setiap kali Anda membuat fitur CRUD baru (misal: **Master Dokter**, **Master Poli**, dll):

```vue
<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

// 1. Terima props dari Controller
const props = defineProps({
    items: Object, // Ubah sesuai nama key dari Inertia::render (misal: 'doctors', 'medicines')
});

// 2. State Modal
const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedId = ref(null);

// 3. Kolom Form (sesuaikan dengan tabel DB)
const form = useForm({
    name: '',
    code: '',
    status: 'active',
});

// 4. Modal Triggers
const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    isEditMode.value = true;
    selectedId.value = item.id;
    form.clearErrors();

    // Isi field form dari baris data yang dipilih
    form.name = item.name;
    form.code = item.code;
    form.status = item.status;

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

// 5. Submit Form (POST & PUT)
const submitForm = () => {
    const endpoint = '/nama-fitur'; // Sesuaikan rute di web.php

    if (isEditMode.value) {
        form.put(`${endpoint}/${selectedId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(endpoint, {
            onSuccess: () => closeModal(),
        });
    }
};

// 6. Delete Action
const deleteItem = (item) => {
    if (confirm(`Yakin ingin menghapus ${item.name}?`)) {
        router.delete(`/nama-fitur/${item.id}`);
    }
};
</script>
```

---

## 4. Checklist Langkah Kerja Saat Bikin Fitur Baru

Saat Anda ingin membuat halaman Index fitur baru dari nol:

1. **Backend**:
    - Buat Migration & Model (`php artisan make:model NamaModel -m`).
    - Buat Controller & Form Request (`php artisan make:controller NamaController --resource`).
    - Daftarkan route di `routes/web.php` (`Route::resource('nama-fitur', NamaController::class)`).
2. **Frontend**:
    - Buat file page di `resources/js/pages/NamaFitur/Index.vue`.
    - Salin kerangka blueprint script di atas, sesuaikan nama kolom dan rute URL-nya.
    - Hubungkan tombol-tombol template ke fungsi:
        - Tombol Tambah: `@click="openAddModal"`
        - Looping Tabel: `v-for="item in items.data"`
        - Tombol Edit di tabel: `@click="openEditModal(item)"`
        - Tombol Hapus di tabel: `@click="deleteItem(item)"`
        - Form Modal: `@submit.prevent="submitForm"`
        - Input data: `v-model="form.nama_kolom"`
        - Pesan error: `<span v-if="form.errors.nama_kolom">{{ form.errors.nama_kolom }}</span>`
