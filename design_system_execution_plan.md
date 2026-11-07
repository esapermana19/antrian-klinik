# 🎨 Sistem Desain UI: Aplikasi Antrean & Klinik Medis

Dokumen ini adalah panduan gaya (style guide) untuk memastikan konsistensi antarmuka (UI) di seluruh halaman proyek Laravel + Vue (Inertia) menggunakan Tailwind CSS.

## 1. Tema Utama (Core Theme)
**"Modern Medical & Clean Bento Grid"**
Fokus pada keterbacaan data medis, kebersihan tata letak menggunakan kartu (cards), dan bayangan lembut (soft shadows) tanpa menggunakan border kasar.

## 2. Palet Warna (Tailwind Config)
*   **Background (Latar Belakang):** `bg-slate-50` (`#F8FAFC`)
*   **Surface (Kartu/Modal):** `bg-white` (`#FFFFFF`)
*   **Sidebar Container:** `bg-slate-950` (`#020617`) atau `bg-gray-900`
*   **Primary (Warna Aksen Utama):** Indigo/Ungu (Tombol utama, teks aktif, menu aktif)
    *   Primary base: `bg-indigo-600`
    *   Primary hover: `bg-indigo-700`
    *   Primary soft (Background icon): `bg-indigo-50`
*   **Teks:**
    *   Heading/Penting: `text-slate-900` (`#0F172A`)
    *   Secondary/Label: `text-slate-500` (`#64748B`)
*   **Status Colors (Pastel Soft):**
    *   Success (Selesai/Obat Tersedia): `text-emerald-700 bg-emerald-50`
    *   Warning (Checking/Antre): `text-amber-700 bg-amber-50`
    *   Danger (Batal/Stok Habis): `text-rose-700 bg-rose-50`

## 3. Tipografi & Radius
*   **Font Family:** `Inter`, `Plus Jakarta Sans`, atau `Poppins`.
*   **Border Radius:**
    *   Card / Modal: `rounded-2xl` atau `rounded-3xl` (16px - 24px)
    *   Button: `rounded-full` atau `rounded-xl`
    *   Sidebar: `rounded-[2rem]` (Kapsul panjang seperti di referensi)
*   **Shadow:** `shadow-sm` atau shadow kustom yang sangat blur dan transparan (`box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05)`).

---

## 4. Panduan Layout per Menu (Menu Mapping)

### A. Sidebar & Topbar (Layout Utama)
*   **Sidebar:** Lebar sekitar 80px-250px. Berwarna gelap, tidak menempel pada pinggir layar kiri (ada margin kecil sehingga terlihat mengambang). Ikon menu yang aktif memiliki background ungu (`bg-indigo-600 text-white`).
*   **Topbar:** Latar transparan/putih. Berisi sapaan "Selamat Pagi, Dr. [Nama]" di kiri, dan *Profile Picture* di kanan.

### B. Dashboard (`/dashboard`)
*   Gunakan Bento Grid layout.
*   Baris atas: 4 Kartu Statistik (Total Pasien, Antrean Selesai, dll) dengan ikon berwarna di dalam lingkaran soft pastel.
*   Baris bawah: Grafik kunjungan mingguan (Line chart) dan Daftar singkat Pasien Hari ini (List view dengan Avatar).

### C. Kelola Antrean (`/queue-management`)
*   Tabel dibungkus di dalam satu Card putih besar `rounded-2xl`.
*   Header tabel tidak menggunakan warna solid tebal, cukup teks `text-slate-500 uppercase text-xs font-semibold`.
*   Status antrean menggunakan gaya Badges/Pill (melengkung `rounded-full` dengan warna pastel).
*   **Modal Rekam Medis:** Harus tampil di tengah, `rounded-2xl`, tanpa border luar tebal, tombol aksi diletakkan di bawah (Primary ungu untuk simpan, outline abu-abu untuk batal).

### D. Pasien, Kategori Obat & Data Obat
*   Sama seperti tabel antrean (Tabel dalam Card putih bersih).
*   Tombol "Tambah Data" berada di pojok kanan atas tabel, menggunakan tombol `bg-indigo-600 rounded-full`.
*   Modal riwayat medis pasien menggunakan desain *timeline* atau list *card* ke bawah.

### E. Halaman TV Antrean (`/display-queues`)
*   Background gradient lembut (`bg-gradient-to-br from-indigo-50 to-white`).
*   Kartu Antrean Saat Ini: Bayangan lebih tebal (`shadow-lg`), border tipis warna primary. Teks super besar agar terbaca dari jauh.
*   Kartu Antrean Selanjutnya: Bayangan tipis, transparan.

---

## 5. Rencana Eksekusi (To-Do List Action Plan)

### Fase 1: Setup Foundation (CSS & Layout Core)
- [x] Install font (Plus Jakarta Sans).
- [x] Konfigurasi `tailwind.config.js` / `app.css` (tambahkan warna kustom tema slate-50, indigo-600, dan soft-shadow kustom).
- [x] Refactor `AppSidebar.vue` agar memiliki tema *Dark* (hitam/navy slate-950) dan berbentuk melengkung (kapsul) mengambang dengan state menu aktif indigo.
- [x] Refactor Topbar/Header layout (Sapaan dinamis waktu "Selamat Pagi/Siang/Sore/Malam, [Nama]" & Avatar pengguna).

### Fase 2: UI Komponen Dasar (Base Components)
- [x] Buat / styling komponen `Card.vue` standar (Putih, `rounded-2xl`, `shadow-soft`, border slate-100 lembut).
- [x] Styling standar Button primer (Indigo `bg-indigo-600`, `rounded-xl`/`pill`, `shadow-sm`) dan Button sekunder.
- [x] Styling standar Tabel (Hilangkan border vertical kasar, buat set komponen Table dengan `border-b border-slate-100`, header `text-slate-500 uppercase text-xs`).
- [x] Styling Badges Status (Pastel soft: Emerald untuk selesai/tersedia, Amber untuk antre/checking, Rose untuk batal/habis, Indigo untuk default).

### Fase 3: Refactor Halaman Master Data
- [x] Refactor Halaman Pasien (Tabel modern tanpa border kasar & Modal Riwayat rekam medis bento timeline).
- [x] Refactor Halaman Obat & Kategori Obat (Card, badge stok, form modal clean).
- [x] Refactor Halaman Jadwal Praktik (Badge status buka/tutup, modal jadwal terstruktur).

### Fase 4: Refactor Halaman Transaksional
- [x] Refactor Halaman Dashboard (Gunakan Bento Grid layout untuk 4 card summary, antrean aktif, dan alert stok obat).
- [x] Refactor Kelola Antrean (Rapikan tabel dan **Desain Ulang Pop-up Modal Rekam Medis** agar elegan dengan 3 kolom vital signs & peresepan obat dinamis).
- [x] Refactor Antrean Saya / Ambil Antrean (Sisi User dengan kartu tiket bergaya modern).
- [x] Refactor TV Display Antrean (Cocokkan dengan tema pastel/indigo dengan jumbo number display & countdown live).