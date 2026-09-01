# Kampus LMS

**Kampus LMS (Learning Management System)**

Kampus LMS merupakan aplikasi berbasis web yang dikembangkan untuk membantu pengelolaan proses pembelajaran, seperti pengelolaan pengguna, mata kuliah, materi pembelajaran, dan aktivitas akademik.

---

## Daftar Anggota Kelompok

| No | Nama Anggota | NIM |
|----|--------------|-----|
| 1 | ..... | 123456789 |
| 2 | Fatika Rizki Syahada | 10241030 |
| 3 | Indriani Anwar | 10241036 |
| 4 | Nama Anggota 4 | 123456789 |

---

## Cara Instalasi

| No | Langkah | Perintah / Keterangan |
|----|---------|----------------------|
| 1 | Clone repository | `git clone <url-repository>` |
| 2 | Masuk ke folder project | `cd kampuslms` |
| 3 | Install dependency Laravel | `composer install` |
| 4 | Membuat file environment | `cp .env.example .env` |
| 5 | Generate application key | `php artisan key:generate` |
| 6 | Konfigurasi database | Atur `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada file `.env` |
| 7 | Menjalankan migration database | `php artisan migrate` |
| 8 | Menjalankan aplikasi | `php artisan serve` |

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
| Frontend | Blade Template, HTML, CSS, JavaScript |