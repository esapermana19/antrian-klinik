# 🏥 Sistem Antrean Klinik & Rekam Medis Digital (Multi-Dokter)

Aplikasi manajemen antrean klinik modern, rekam medis terpadu, resep obat otomatis, kuitansi/invoice, dan display antrean layar TV (kiosk) berbasis **Laravel 12**, **Vue 3 (Inertia.js v3)**, dan **Tailwind CSS v4**.

Dirancang fleksibel untuk dapat digunakan oleh berbagai jenis klinik (klinik pratama, klinik dokter bersama/multi-dokter, dokter gigi, poliklinik spesialis).

---

## 🌟 Fitur Utama

- **🩺 Multi-Dokter & Multi-Poli:**
  - Manajemen dokter (nama, spesialisasi, no. STR, foto profil, dan status aktif).
  - Jadwal praktik terdistribusi per dokter (sesi pagi dan sore) dilengkapi kuota dan jam buka/tutup.
  - Pencarian dan filter dokter pada pemesanan antrean maupun tabel jadwal operasional.
- **🎟️ Layanan Antrean Pasien:**
  - Pasien dapat memilih tanggal, sesi, dokter/poli, dan slot jam periksa.
  - Fitur penguncian slot otomatis (*temporary cache slot lock*) mencegah perebutan tiket antrean oleh 2 pengguna bersamaan.
  - Menu khusus pasien untuk memantau status antrean aktifnya secara real-time.
- **📺 Display Antrean TV / Kiosk Mode:**
  - Tampilan monitor antrean khusus layar TV ruang tunggu tanpa scrollbar (*fit to screen*).
  - Polling super cepat (tiap 1.5 detik) tanpa reload halaman.
  - Pemilihan filter display per dokter/poli maupun gabungan poliklinik.
  - Mode layar penuh (*Fullscreen Kiosk*), jam digital real-time, dan estimasi hitung mundur durasi pemeriksaan.
- **📋 Rekam Medis & Farmasi Terpadu:**
  - Input TTV (Tanda-Tanda Vital: Tekanan Darah, Suhu, Berat Badan) dan diagnosis tindakan.
  - Resep obat otomatis memotong stok obat secara aman (*pessimistic locking*).
  - Riwayat rekam medis pasien dengan tampilan accordion ringkas.
- **💳 Kasir & Kuitansi / Invoice:**
  - Generate nota otomatis menggabungkan tarif pemeriksaan dan total biaya obat.
  - Layout cetak faktur invoice hitam-putih formal standar A4 rapi dengan kop identitas dan logo resmi klinik.
- **⚙️ Pengaturan Identitas & Logo Klinik:**
  - Kustomisasi nama klinik, alamat, telepon, dan logo resmi dari dashboard admin tanpa utak-atik kode.
  - Logo otomatis tampil di sidebar, invoice resmi, dan display antrean TV.
- **👥 Manajemen Pengguna & Keamanan:**
  - Manajemen staf & admin klinik, ganti peran akses (*Role-Based Access Control*), dan reset password instan oleh admin.
  - Autentikasi lengkap: Dua Faktor (2FA), Passkey (WebAuthn), dan Email Verification.

---

## 💻 Kebutuhan Sistem (System Requirements)

Pastikan lingkungan server Anda telah terpasang:
- **PHP** >= 8.2 (Direkomendasikan PHP 8.2, 8.3, atau 8.4)
- Ekstensi PHP wajib: `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `PDO_MySQL`, `Tokenizer`, `XML`, `GD` (untuk pemrosesan upload gambar/logo)
- **Composer** >= 2.x
- **Node.js** >= 18.x & **NPM**
- **MySQL** >= 8.0 atau **MariaDB** >= 10.4

---

## 🚀 Panduan Instalasi (Step-by-Step)

### 1. Ekstrak Source Code
Ekstrak folder source code ke direktori web server Anda (misal `C:\laragon\www\antrian-klinik` atau `/var/www/antrian-klinik`).

### 2. Install Dependensi PHP & Frontend
Buka terminal / command prompt di folder project, lalu jalankan:
```bash
composer install
npm install
```

### 3. Konfigurasi Environment (`.env`)
Salin file konfigurasi contoh `.env.example` menjadi `.env`:
```bash
# Windows PowerShell / CMD:
copy .env.example .env

# Linux / macOS:
cp .env.example .env
```

Buka file `.env` dan sesuaikan koneksi database Anda:
```env
APP_NAME="Antrian Klinik"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_antrian_klinik
DB_USERNAME=root
DB_PASSWORD=
```
*(Pastikan Anda telah membuat database baru di MySQL dengan nama `db_antrian_klinik`)*

### 4. Generate App Key & Storage Link
Jalankan perintah berikut untuk mengunci enkripsi aplikasi dan menghubungkan folder publik storage:
```bash
php artisan key:generate
php artisan storage:link
```

### 5. Migrasi Database & Seeder Data Contoh (Demo)
Jalankan migrasi dan seeder untuk langsung mengisi data klinik contoh, akun admin, dokter spesialis, obat, jadwal, dan antrean hari ini:
```bash
php artisan migrate --seed
```

### 6. Build Aset Frontend
Kompilasi aset Vue & Tailwind untuk mode produksi:
```bash
npm run build
```

### 7. Jalankan Server Aplikasi
Jalankan server pengembangan Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui browser di: **`http://localhost:8000`**

---

## 🔑 Akun Bawaan (Default Demo Credentials)

Setelah menjalankan `php artisan migrate --seed`, Anda dapat langsung login dengan akun berikut:

| Peran Akun | Email Login | Password | Akses / Halaman |
|---|---|---|---|
| **Administrator Klinik** | `admin@klinik.test` | `password` | Dashboard Lengkap, Antrean, Dokter, Pengguna, Jadwal, Obat, Invoice, & Pengaturan |
| **Pasien Demo** | `user@klinik.test` | `password` | Ambil Antrean & Antrean Saya |

---

## 📧 Pengaturan Pengiriman Email (Email Verification)

Secara bawaan lokal, email menggunakan driver log. Jika ingin mengaktifkan pengiriman email verifikasi asli melalui **Gmail SMTP**, atur di file `.env`:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=app_password_16_digit_dari_google
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@klinik.test"
MAIL_FROM_NAME="${APP_NAME}"
```

> **Catatan Gmail:** `MAIL_PASSWORD` bukan password akun biasa, melainkan **App Password (Sandi Aplikasi)** 16 digit yang dibuat melalui menu *Google Account Security > 2-Step Verification > App Passwords*.

---

## ⚠️ Troubleshooting & Masalah Umum

### 1. Pesan `403 | Invalid signature` saat Verifikasi Email di Ngrok
- **Penyebab:** URL yang diakses (`https://xxxx.ngrok-free.dev`) berbeda dengan parameter `APP_URL` di file `.env`.
- **Solusi:**
  1. Ubah baris `APP_URL` di `.env` menjadi domain ngrok Anda:
     ```env
     APP_URL=https://xxxx.ngrok-free.dev
     ```
  2. Jalankan perintah `php artisan config:clear`.
  3. Buka halaman verifikasi dan klik tombol **Resend verification email**.

### 2. Gambar / Logo Tidak Muncul di Browser
- **Solusi:** Pastikan Anda telah menjalankan perintah:
  ```bash
  php artisan storage:link
  ```

### 3. Tampilan Frontend Tidak Berubah atau Error Vite Manifest
- **Solusi:** Jalankan perintah build ulang:
  ```bash
  npm run build
  ```
  atau jalankan server pengembang aktif selama proses koding:
  ```bash
  npm run dev
  ```

---

## 📄 Lisensi & Komersialisasi
Source code ini siap digunakan untuk operasional klinik maupun dijual kembali melalui platform digital (seperti Lynk.id). Bebas dikembangkan sesuai kebutuhan masing-masing klinik.
