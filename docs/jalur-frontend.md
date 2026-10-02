# Formulir Pendaftaran Jalur Frontend (Minggu 7–16)
### Mata Kuliah: Pemrograman Web
### Program Studi Sistem Informasi / Informatika — Institut Teknologi Kalimantan

---

## 1. Identitas Kelompok

- **Kelas**: B
- **Kelompok**: 03
- **Nama Repository**: `kampuslms-b-kelompok-03`
- **Tautan Repository**: `https://github.com/FalihDzakwan1/kampuslms-b-kelompok-03`

### Anggota Kelompok & Pembagian Peran

| No | Nama Lengkap | NIM | Peran Utama | Tanggung Jawab Utama |
|---|---|---|---|---|
| 1 | **Fatika Rizki Syahada** | 10241030 | **Lead Frontend Developer** | Merancang UI/UX, komponen Blade, layout responsif Tailwind CSS, serta integrasi interaksi JavaScript. |
| 2 | **Elsya Nur Aulia Handayani** | 10241026 | **Database & UI Support** | Pengelolaan relasi data, seeder pengujian UI, dan integrasi data view. |
| 3 | **Falih Dzakwan** | 10241028 | **Backend & API Integrator** | Penyediaan endpoint API, token Sanctum, logic controller, dan penghubung API ke frontend. |
| 4 | **Indriani Anwar** | 10241036 | **Backend & QA Developer** | Validasi otorisasi data, pengujian end-to-end, dan penanganan error handling pada form frontend. |

---

## 2. Jalur Frontend yang Dipilih

> **Jalur yang Didaftarkan**:  
> **Laravel Blade + Tailwind CSS v4 + Vanilla JS / Axios (Hybrid Monolith & REST API Consumer)**

### Deskripsi & Rasionalitas Pemilihan Jalur:
1. **Blade Components Modular**: Memanfaatkan arsitektur komponen Laravel Blade untuk reusable UI (seperti button, modal, card, navbar, alert, form input) sehingga antarmuka bersih dan konsisten.
2. **Tailwind CSS v4 & Vite**: Memberikan pengalaman styling modern yang responsif, adaptif di semua resolusi perangkat (mobile, tablet, desktop), dengan proses kompilasi cepat via Vite.
3. **Integrasi REST API via Axios**: Halaman-halaman dinamis (seperti pengumpulan tugas async, penilaian instan, filter mata kuliah tanpa reload halaman, dan polling notifikasi) langsung mengonsumsi endpoint `/api/v1/*` yang telah diamankan Sanctum dan dilindungi oleh sistem rate limiting.
4. **Keamanan & Efisiensi**: Menggabungkan perlindungan bawaan Laravel (CSRF protection, sanitasi Blade) dengan performa dan fleksibilitas konsumsi API.

---

## 3. Rencana Roadmap Pengembangan Frontend (Minggu 7–16)

| Minggu Ke- | Target Pengembangan Frontend | Fitur Utama | Penanggung Jawab |
|---|---|---|---|
| **Minggu 7** | **Design System & Auth UI** | Setup layout master, tema Tailwind, login UI, registrasi/profil, dan proteksi rute berbasis role. | Fatika Rizki Syahada |
| **Minggu 8** | **Dashboard Mahasiswa & Dosen** | Halaman ringkasan akademik: status mata kuliah aktif, jadwal tugas mendekati deadline, dan statistik nilai. | Fatika & Falih |
| **Minggu 9** | **Modul Mata Kuliah & Materi** | Katalog mata kuliah, halaman detail per mata kuliah, preview materi, dan pengunggahan berkas bahan ajar (PDF/Slide). | Fatika & Elsya |
| **Minggu 10** | **Modul Manajemen Penugasan (Dosen)** | Form pembuatan tugas baru, konfigurasi deadline & allow late, rich text editor panduan tugas, dan publikasi/draft. | Indriani & Fatika |
| **Minggu 11** | **Modul Pengumpulan Tugas (Mahasiswa)** | Form upload berkas submission (drag & drop, validasi ekstensi & ukuran maks 10MB), status terlambat, dan catatan mahasiswa. | Fatika & Indriani |
| **Minggu 12** | **Modul Penilaian & Review (Dosen)** | Tampilan daftar pengumpulan berkas mahasiswa, preview jawaban, modal input nilai (0-100), dan input feedback koreksi. | Falih & Fatika |
| **Minggu 13** | **Rekapitulasi Nilai & Transkrip Tugas** | Tampilan transkrip nilai per mahasiswa, rekapitulasi nilai kelas untuk dosen, serta ekspor laporan. | Elsya & Indriani |
| **Minggu 14** | **Pusat Notifikasi & Riwayat Aktivitas** | Dropdown interaktif notifikasi baru, status unread badge, dan fungsi tandai telah dibaca (*mark as read*). | Falih & Fatika |
| **Minggu 15** | **Polish UI/UX, Aksesibilitas, & Dark Mode** | Pengujian lintas peramban, optimasi layout mobile (responsif), perbaikan mikro-interaksi, dan animasi transisi. | Seluruh Anggota |
| **Minggu 16** | **Integrasi Final & Persiapan Presentasi** | Pengujian akhir end-to-end, freeze kode, dokumentasi user guide, dan persiapan demo sistem kepada dosen pengampu. | Seluruh Anggota |

---

## 4. Pengesahan Pendaftaran

Formulir ini diajukan sebagai pendaftaran resmi jalur pengerjaan frontend kelompok untuk evaluasi paruh kedua semester (Minggu 7 s/d Minggu 16).

Balikpapan, 2 Oktober 2026  
**Perwakilan Kelompok 03 (Kelas B):**

*(Tertanda)*  
**Falih Dzakwan / Fatika Rizki Syahada**  
NIM. 10241028 / 10241030

---
**Mengetahui / Menyetujui:**  
Dosen Pengampu Mata Kuliah Pemrograman Web
