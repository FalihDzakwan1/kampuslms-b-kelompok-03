
# Kampus LMS

**Kampus LMS (Learning Management System)**

Kampus LMS merupakan aplikasi berbasis web yang dikembangkan untuk membantu pengelolaan proses pembelajaran, seperti pengelolaan pengguna, mata kuliah, materi pembelajaran, dan aktivitas akademik.

---

## Daftar Anggota Kelompok

| No | Nama Anggota | NIM |
|----|--------------|-----|
| 1 | Elsya Nur Aulia Handayani | 10241026 |
| 2 | Falih Dzakwan | 10241028 |
| 3 | Fatika Rizki Syahada | 10241030 |
| 4 | Indriani Anwar | 10241036 |


---

## Cara Instalasi

| No | Langkah | Perintah / Keterangan |
|----|---------|----------------------|
| 1 | Masuk ke folder project | `cd kampuslms` |
| 2 | Install dependency Laravel | `composer install` |
| 3 | Install laravel 12 | `composer create-project laravel/laravel:^12.0 kampuslms-b-kelompok-03` |
| 4 | Konfigurasi database | Atur `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada file `.env` |
| 5 | Menjalankan migration database | `php artisan migrate` |
| 6 | Menjalankan aplikasi | `php artisan serve` |

Aplikasi dapat diakses melalui:


---

## Pembagian Peran Anggota

| No | Nama Anggota | Peran | Tanggung Jawab |
|----|--------------|-------|----------------|
| 1 | Fatika Rizki Syahada | Frontend Developer | Mengembangkan tampilan antarmuka aplikasi menggunakan Blade, HTML, CSS, dan JavaScript |
| 2 | Elsya Nur Aulia Handayani | Database Developer | Mengelola struktur database, membuat migration, seeder, dan memastikan pengelolaan data aplikasi |
| 3 | Falih Dzakwan | Backend Developer | Mengembangkan logika aplikasi, controller, model, routing, dan integrasi sistem Laravel |
| 4 | Indriani Anwar | Backend Developer | Membantu pengembangan backend, pengelolaan fitur aplikasi, dan implementasi fungsi Laravel |
---

## Teknologi yang Digunakan

| Teknologi | Versi |
|-----------|-------|
| Laravel | 12.68.0|
| PHP | 8.5.10 |
| MySQL | 8.0.40 |
| Composer | Latest |
| Frontend | Blade Template, Tailwind CSS v4, JavaScript, Axios |

---

## Jalur Frontend (Minggu 7–16)
Kelompok memilih dan mendaftarkan jalur **Blade Template + Tailwind CSS v4 + Vanilla JS / Axios (Hybrid Monolith & REST API Consumer)**.  
Detail dokumen pendaftaran resmi dan roadmap pengerjaan dapat dilihat di:  
📄 **[Dokumen Pendaftaran Jalur Frontend](docs/jalur-frontend.md)**

---

## Dokumentasi & Pengujian API
- **Dokumentasi REST API & Rate Limiting**:  
  Seluruh endpoint API v1 (Auth, Courses, Assignments, Submissions, Grades, Notifications) dengan rate limiting (60/menit umum, 5/menit login) dan contoh cURL terdokumentasi di:  
  📄 **[Dokumentasi API Kampus LMS](docs/api.md)**

- **Skrip Pengujian Otorisasi (Prompt C)**:  
  Skrip pengujian otomatis untuk memvalidasi proteksi autentikasi (401), RBAC (403), IDOR prevention (403), request valid (200/201), dan rate limiting (429):  
  💻 **[scripts/test-api.sh](scripts/test-api.sh)**  
  Cara menjalankan:
  ```bash
  chmod +x scripts/test-api.sh
  ./scripts/test-api.sh
  ```

