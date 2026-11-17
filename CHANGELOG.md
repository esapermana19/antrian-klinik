# Changelog

Seluruh pembaruan, perbaikan, dan penambahan fitur pada proyek Sistem Antrean Klinik didokumentasikan di sini.

## [v1.1.0] - 2026-11-17

### Ditambahkan

- **Penyempurnaan Export Excel & Cetak Rekap Laporan Operasional:**
    - Export berkas Excel resmi `.xlsx` berformat tabel (bukan lagi raw plain CSV text): dilengkapi kop nama klinik, header kolom indigo dengan teks tebal, pemisah border tabel rapi, alignment nomor dan teks proporsional, format angka mata uang rupiah (`#,##0`), serta baris akumulasi total pendapatan otomatis.
    - Halaman cetak laporan baru di `/reports/print` (Clean Print View): otomatis menghilangkan sidebar, topbar, dan scrollbar horizontal; memformat tata letak kertas cetak Landscape standar dokumen resmi klinik lengkap dengan kop surat logo klinik, tabel ringkasan keuangan, serta lembar tanda tangan Kepala Klinik/Penanggung Jawab.
- **Format Nomor Antrean Kustom & Prefiks Poli/Dokter (P2-E):**
    - Pengaturan `queue_prefix` default klinik pada `/settings/clinic` (contoh: `A`, `KLN`).
    - Kolom `queue_prefix` pada tabel dokter untuk penamaan tiket spesifik per poli/spesialisasi (contoh: `GGI-001`, `ANK-001`, `INT-001`).
    - Nomor antrean lebih rapi dengan format 3 digit (`PREFIX-001`) dan mudah dibedakan saat dipanggil di Display TV Kiosk maupun tercetak di Invoice.
- **Peringatan & Manajemen Stok Minimum Obat (P2-D):**
    - Kolom `min_stock` pada tabel `medications` dengan default batas minimum.
    - Scope Eloquent `lowStock()` untuk query otomatis obat yang membutuhkan restock.
    - Badge peringatan merah dinamis di sidebar menu Master Data & Data Obat.
    - Card peringatan "Perlu Restock Obat" di Dashboard yang langsung terhubung ke filter obat menipis.
    - Filter quick-switch "Stok Menipis" dan penandaan visual baris obat pada tabel Master Data Obat.
    - Input batas stok minimum pada form penambahan dan pengubahan obat.
- **Tarif Pemeriksaan Dokter Default (P2-C):**
    - Pengaturan tarif pemeriksaan standar klinik pada `/settings/clinic`.
    - Otomatisasi pengisian tarif pemeriksaan pada pembuatan rekam medis dan invoice tagihan.
- **Laporan Kunjungan & Finansial dengan Export Excel (P2-B):**
    - Halaman Laporan di `/reports` dengan filter rentang tanggal dan dokter.
    - Kartu ringkasan total pendapatan, jasa periksa, obat, dan statistik kunjungan.
    - Fitur export data laporan ke format CSV ber-BOM UTF-8 (kompatibel penuh dengan Microsoft Excel).
- **Pengumuman Suara Panggilan Antrean Otomatis / TTS (P2-A):**
    - Pemanggilan nomor antrean, nama pasien, dan ruang dokter bersuara Bahasa Indonesia di Display TV Kiosk.
    - Nada panggilan periksa (_harmonic dual chime_) berbasis Web Audio API tanpa aset eksternal.
- **Fitur Multi-Dokter & Multi-Poli:**
    - Model `Doctor` dan tabel database `doctors` lengkap dengan STR, kontak, dan spesialisasi.
    - Relasi `doctor_id` pada jadwal praktik, antrean pasien, dan rekam medis.
    - CRUD manajemen dokter untuk administrator di `/doctor`.
    - Filter dokter pada antrean pasien dan jadwal operasional.
    - Dukungan filter per poli/dokter pada monitor display antrean TV kiosk.
- **Manajemen Pengguna oleh Admin:**
    - CRUD pengguna klinik di `/users` dengan filter role.
    - Fitur reset kata sandi langsung oleh administrator.
    - Proteksi pencegahan penghapusan akun mandiri.
- **Identitas & Logo Klinik Fleksibel:**
    - Fitur upload logo klinik resmi pada halaman pengaturan `/settings/clinic`.
    - Tampilan logo otomatis pada header topbar, nota invoice cetak, dan display TV antrean.
- **Demo Seeder Lengkap:**
    - `DoctorSeeder`: 4 data dokter spesialis siap pakai.
    - `MedicationCategorySeeder`: 5 kategori dan 15 data obat berstok.
    - `PracticeScheduleSeeder`: Jadwal sesi pagi dan sore setiap hari per dokter.
    - `QueueAndMedicalRecordSeeder`: Data antrean realistis (completed, checking, called, waiting) beserta rekam medis, resep, dan invoice lunas.
- **Dokumentasi Lengkap:**
    - File `README.md` panduan instalasi langkah-demi-langkah, konfigurasi database, SMTP email, kredensial demo, dan solusi troubleshooting.

### Diperbaiki

- Sinkronisasi tata letak antarmuka manajemen pengguna dan dokter agar konsisten dengan master data pasien.
- Penanganan trusted proxies dan validasi URL bertanda tangan (signed URL) untuk mencegah error `403 Invalid signature` saat diakses via reverse proxy/ngrok.
- Input pencarian dokter pada jadwal praktik klinik.

---

## [v1.0.0] - 2026-10-15

### Rilis Perdana

- Sistem antrean antarmuka Inertia.js v3 + Vue 3.
- Rekam medis pasien & pemotongan stok farmasi otomatis.
- Cetak faktur invoice pasien.
- Autentikasi 2FA, Passkey, dan verifikasi email Laravel Fortify.
