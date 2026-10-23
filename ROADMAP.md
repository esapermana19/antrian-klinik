# 📋 Panduan & Flow Pengerjaan Mandiri: Sistem Antrean Klinik

Dokumen ini disusun sebagai panduan pengerjaan manual langkah demi langkah (_step-by-step roadmap_) dengan standar **Laravel 13 & Vue 3 Inertia Best Practices**. Gunakan checklist `[ ]` untuk menandai kemajuan pengerjaan Anda.

---

## 🏛️ Arsitektur & Prinsip Best Practice

1. **Thin Controller**: Controller hanya mengorkestrasi request, validasi, dan response. Logika bisnis rumit atau kalkulasi dipindahkan ke Action Class atau Model method.
2. **Form Request**: Setiap data masukan dari form wajib divalidasi menggunakan class Form Request (`php artisan make:request`), bukan `$request->validate()` di dalam controller.
3. **Database Transaction (`DB::transaction`)**: Wajib digunakan untuk proses yang mengubah lebih dari satu tabel sekaligus (misal: simpan resep obat + potong stok obat).
4. **Route Model Binding**: Gunakan type-hinting model di controller (`show(Patient $patient)`), hindari `find($id)` manual.
5. **Laravel Wayfinder**: Impor fungsi rute di Vue dari `@/actions/...` atau `@/routes/...` agar terhubung langsung dengan backend tanpa hardcoded URL.

---

## 🗺️ Roadmap Pengerjaan

```
Fase 1: Factories & Seeders  ──> Menyiapkan data dummy uji coba realistis
       │
Fase 2: Master Data Mandiri  ──> Jadwal Dokter & Obat (CRUD dasar)
       │
Fase 3: Manajemen Pasien     ──> Pendaftaran & profil pasien
       │
Fase 4: Sistem Antrean Inti  ──> Booking antrean, kuota, nomor tiket, & estimasi waktu
       │
Fase 5: Rekam Medis & Resep  ──> Pemeriksaan dokter & transaksi potong stok obat
       │
Fase 6: Tagihan & Kasir      ──> Perhitungan biaya & status pembayaran
       │
Fase 7: Otorisasi & Policy   ──> Membatasi akses Admin vs Pasien
```

---

## 🚀 FASE 1: Factory & Database Seeder (Data Simulator)

Tujuan: Memiliki data dummy yang konsisten dan mudah di-_reset_ ulang kapan saja dengan `php artisan migrate:fresh --seed`.

- [ ] **1.1 Buat Factory untuk Master Data**
    - Jalankan command:
        ```bash
        php artisan make:factory PracticeScheduleFactory
        php artisan make:factory MedicationFactory
        php artisan make:factory PatientFactory
        ```
- [ ] **1.2 Definisikan Dummy Data pada Factory**
    - `PracticeScheduleFactory`: Isi `schedule_code` (misal: `P01`), `day` (`monday`-`sunday`), `session` (`morning`/`afternoon`), `opening_hours`, `closing_hours`, `max_quota` (misal: 20-30), `status` (`open`).
    - `MedicationFactory`: Isi `medication_code`, `medication_name`, `stock` (misal: 50-100), `unit_price` (misal: 10000 - 50000).
    - `PatientFactory`: Isi `user_id` (menggunakan `User::factory()`), `nik` (16 digit acak), `patient_name`, `date_of_birth`, `gender`.
- [ ] **1.3 Konfigurasi `DatabaseSeeder.php`**
    - Buat 1 User Admin: `role => 'administrator'`
    - Buat 1 User Pasien: `role => 'user'`
    - Buat jadwal praktek untuk hari Senin - Sabtu.
    - Buat 10-15 data obat awal.
- [ ] **1.4 Uji Coba Seeder**
    - Jalankan `php artisan db:seed` atau `php artisan migrate:fresh --seed` dan pastikan data masuk tanpa error.

---

## 📦 FASE 2: Master Data Mandiri (Jadwal Praktek & Obat)

Tujuan: Mengimplementasikan CRUD data master yang tidak memiliki ketergantungan pada tabel transaksi lain.

### 2.A Jadwal Praktek (`PracticeSchedule`)

- [x] **2.A.1 Form Request Validasi**
    - `php artisan make:request StorePracticeScheduleRequest`
    - `php artisan make:request UpdatePracticeScheduleRequest`
    - Validasi: `day` (enum valid), `session`, `opening_hours`, `closing_hours`, `max_quota` (`integer|min:1`), kombinasi unik `day + session`.
- [x] **2.A.2 Lengkapi `PracticeScheduleController.php`**
    - `index()`: Mengambil list jadwal dengan paginasi dan pengurutan hari/sesi, render ke Vue `Inertia::render('PracticeSchedules/Index', [...])`.
    - `store()`: Auto-generate `schedule_code` (misal: `MON-1-S01`) dan simpan jadwal baru via Form Request.
    - `update()`: Update jadwal via Form Request.
    - `destroy()`: Hapus jadwal dengan proteksi cek relasi antrean aktif (`$practiceSchedule->queues()->exists()`).
- [x] **2.A.3 Frontend Halaman Jadwal**
    - Buat halaman tabel jadwal di `resources/js/pages/PracticeSchedules/Index.vue` lengkap dengan modal Create/Edit dan konfirmasi hapus.

### 2.B Kategori Obat (`MedicationCategory`)

- [x] **2.B.1 Migrasi & Relasi Tabel**
    - Tabel `medication_categories` (`medication_category_id`, `medication_category_code`, `medication_category_name`).
    - Foreign key `medication_category_id` di tabel `medications` dengan `restrictOnDelete()`.
- [ ] **2.B.2 Form Request Validasi**
    - `StoreMedicationCategoryRequest`: Validasi kode unik 1 karakter (`size:1|unique:medication_categories,medication_category_code`) dan nama kategori.
    - `UpdateMedicationCategoryRequest`: Rule unik dengan ignore ID kategori yang sedang diedit.
- [x] **2.B.3 Lengkapi `MedicationCategoryController.php`**
    - `index()`: Tampilkan list kategori obat dengan paginasi (`paginate(10)`).
    - `store()` & `update()`: CRUD standar via Form Request.
    - `destroy()`: Hapus kategori dengan proteksi jika masih digunakan oleh data obat (`$category->medications()->exists()`).
- [x] **2.B.4 Frontend Halaman Kategori Obat**
    - Buat halaman di `resources/js/pages/MedicationCategories/Index.vue`.

### 2.C Data Obat (`Medication`)

- [x] **2.C.1 Form Request Validasi**
    - `php artisan make:request StoreMedicationRequest`
    - `php artisan make:request UpdateMedicationRequest`
    - Validasi: `medication_category_id` (`required|exists:medication_categories,medication_category_id`), `medication_code` (`unique:medications,medication_code`), `medication_name`, `stock` (`integer|min:0`), `unit_price` (`numeric|min:0`).
- [x] **2.C.2 Lengkapi `MedicationController.php`**
    - Eager load relasi kategori obat: `Medication::with('medicationCategory')->paginate(10)`.
    - Implementasi CRUD standar.
- [x] **2.C.3 Frontend Halaman Obat**
    - Buat halaman manajemen obat di `resources/js/pages/Medications/Index.vue` dengan dropdown pilihan Kategori Obat.

---

## 👤 FASE 3: Manajemen Profil Pasien (`Patient`)

Tujuan: Menghubungkan akun user yang login dengan profil pasien (1 user bisa mendaftarkan dirinya sendiri atau anggota keluarganya).

- [ ] **3.1 Form Request Validasi**
    - `php artisan make:request StorePatientRequest`
    - `php artisan make:request UpdatePatientRequest`
    - Validasi: `nik` (`required|digits:16|unique:patients,nik`), `patient_name`, `date_of_birth`, `gender`.
- [ ] **3.2 Lengkapi `PatientController.php`**
    - `index()`: Tampilkan pasien milik user yang sedang login (jika user biasa) atau semua pasien (jika admin).
    - `store()`:
        ```php
        $request->user()->patients()->create($request->validated());
        ```
    - `show(Patient $patient)`: Tampilkan detail pasien beserta riwayat antrean & rekam medisnya (`load(['queues', 'medicalRecords'])`).
- [ ] **3.3 Frontend Halaman Pasien**
    - Buat form pendaftaran pasien dan daftar anggota keluarga yang didaftarkan.

---

## ⏱️ FASE 4: Sistem Antrean Inti (`Queue`)

Tujuan: Modul utama klinik untuk mengambil nomor antrean, menghitung kuota, dan mengubah status antrean secara _real-time_.

- [ ] **4.1 Logika Bisnis Booking Antrean**
    - Saat pasien memilih jadwal (`schedule_id`) dan tanggal (`queue_date`):
        1. **Cek Kuota**: Hitung jumlah antrean aktif pada jadwal & tanggal tersebut (`Queue::where('schedule_id', ...)->where('queue_date', ...)->count()`). Jika `>= max_quota`, tolak dengan pesan bahwa kuota penuh.
        2. **Generate Nomor Antrean Otomatis**: Misal format `P01`, `P02`, dsb., berdasarkan urutan pada hari dan sesi tersebut.
        3. **Estimasi Waktu**: Jam buka jadwal + (urutan antrean * 15 menit).
        4. **Status Awal**: Set default `waiting`.
- [ ] **4.2 Form Request Validasi**
    - `php artisan make:request StoreQueueRequest`
    - Validasi: `patient_id` (pastikan milik user login), `schedule_id` (jadwal harus `status = open`), `queue_date` (minimal hari ini).
- [ ] **4.3 Lengkapi `QueueController.php`**
    - `index()`: Tampilkan antrean hari ini untuk display ruang tunggu atau dashboard pasien.
    - `store()`: Eksekusi logika booking antrean di atas.
    - `updateStatus(Request $request, Queue $queue)`: Endpoint khusus untuk petugas/dokter memanggil atau memajukan status antrean (`waiting` $\rightarrow$ `called` $\rightarrow$ `checking` $\rightarrow$ `completed` / `cancel`).
- [ ] **4.4 Frontend Halaman Antrean**
    - Halaman Ambil Antrean (untuk Pasien).
    - Halaman Display TV Antrean / Panggilan Antrean (untuk Petugas Klinik).

---

## 🩺 FASE 5: Pemeriksaan Medis & Resep Obat (`MedicalRecord` & `MedicationPrescription`)

Tujuan: Dokter melakukan pencatatan hasil diagnosa dan meresepkan obat, dengan otomatis memotong stok obat di gudang farmasi.

- [ ] **5.1 Form Request Validasi**
    - `php artisan make:request StoreMedicalRecordRequest`
    - Validasi struktur:
        - Data diagnosa: `queue_id`, `patient_id`, `main_complaint`, `blood_pressure`, `temperature`, `weight`, `diagnosis`, `notes_on_treatment`.
        - Data resep (array): `prescriptions.*.medication_id`, `prescriptions.*.qty`, `prescriptions.*.dosage`.
- [ ] **5.2 Transaksi Database (`DB::transaction`) pada `MedicalRecordController@store`**
    ```php
    use Illuminate\Support\Facades\DB;

    DB::transaction(function () use ($validated, $queue) {
        // 1. Buat kode rekam medis unik (misal: RM-20261016-001)
        // 2. Simpan MedicalRecord
        $medrec = MedicalRecord::create([...]);

        // 3. Loop resep obat
        foreach ($validated['prescriptions'] as $item) {
            $medication = Medication::findOrFail($item['medication_id']);

            // Pastikan stok mencukupi
            if ($medication->stock < $item['qty']) {
                throw new \Exception("Stok obat {$medication->medication_name} tidak mencukupi!");
            }

            // Simpan MedicationPrescription
            $medrec->medicationPrescriptions()->create([
                'medication_id' => $medication->medication_id,
                'prescription_code' => 'RSP-' . uniqid(),
                'qty' => $item['qty'],
                'unit_price' => $medication->unit_price,
                'total_price' => $medication->unit_price * $item['qty'],
                'dosage' => $item['dosage'],
            ]);

            // Potong stok obat
            $medication->decrement('stock', $item['qty']);
        }

        // 4. Ubah status antrean menjadi 'completed'
        $queue->update(['queue_status' => 'completed']);
    });
    ```
- [ ] **5.3 Frontend Halaman Dokter**
    - Halaman form input rekam medis + dropdown dinamis memilih obat dan dosis.

---

## 💰 FASE 6: Tagihan & Kasir (`Invoice`)

Tujuan: Mengkalkulasi total biaya pengobatan pasien dan memproses pembayaran.

- [ ] **6.1 Logika Pembuatan Invoice Otomatis**
    - Dapat di-trigger otomatis setelah rekam medis disimpan, atau dibuat via kasir:
        - `examination_fee`: Biaya jasa dokter/klinik tetap (misal: Rp50.000).
        - `medication_fee`: Jumlahkan `total_price` dari seluruh `medicationPrescriptions` pada rekam medis tersebut.
        - `total_amount`: `examination_fee + medication_fee`.
        - `payment_status`: `unpaid`.
- [ ] **6.2 Lengkapi `InvoiceController.php`**
    - `index()`: Daftar tagihan kasir (filter `unpaid` / `paid`).
    - `show(Invoice $invoice)`: Cetak struk/nota pembayaran dengan relasi obat dan pasien.
    - `pay(Request $request, Invoice $invoice)`: Update `payment_status = 'paid'`, catat `payment_method` (`cash`/`transfer`).
- [ ] **6.3 Frontend Halaman Kasir**
    - Tampilan kasir & rincian nota tagihan.

---

## 🔒 FASE 7: Keamanan, Hak Akses & Policy

Tujuan: Mencegah kebocoran data antar pasien dan mengamankan rute backend.

- [ ] **7.1 Buat Authorization Policies**
    ```bash
    php artisan make:policy PatientPolicy --model=Patient
    php artisan make:policy QueuePolicy --model=Queue
    php artisan make:policy MedicalRecordPolicy --model=MedicalRecord
    ```
- [ ] **7.2 Terapkan Aturan Akses**
    - `Administrator`: Akses penuh ke seluruh data.
    - `User`: Hanya boleh melihat data `Patient`, `Queue`, dan `MedicalRecord` yang `user_id`-nya cocok dengan dirinya (`$user->user_id === $patient->user_id`).
- [ ] **7.3 Lindungi Controller**
    - Gunakan `$this->authorize('view', $patient)` atau method bawaan Policy di Controller.

---

## 🧪 FASE 8: Pengujian Mandiri (Feature Tests dengan Pest)

Tujuan: Memastikan fitur tidak rusak saat Anda melakukan perubahan di masa mendatang.

- [ ] **8.1 Buat Test Antrean**
    ```bash
    php artisan make:test --pest QueueBookingTest
    ```
    - Test skenario: User berhasil booking antrean.
    - Test skenario: User gagal booking jika kuota hari itu sudah penuh.
- [ ] **8.2 Buat Test Rekam Medis & Stok Obat**
    ```bash
    php artisan make:test --pest MedicalRecordTest
    ```
    - Test skenario: Stok obat otomatis berkurang saat resep dibuat.
- [ ] **8.3 Jalankan Seluruh Test Suite**
    ```bash
    php artisan test
    ```

---

_Semoga panduan ini membantu Anda belajar ngoding mandiri secara terstruktur dan terbiasa dengan standar industri Laravel!_
