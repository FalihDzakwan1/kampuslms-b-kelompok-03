
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

## Akun Demo

Seluruh akun di bawah ini dibuat oleh seeder dengan kata sandi **`password`**.

| Peran | Email | Kata Sandi |
|-------|-------|------------|
| Admin | `admin@kampuslms.test` | `password` |
| Dosen | `dosen@kampuslms.test` | `password` |
| Mahasiswa | `mahasiswa@kampuslms.test` | `password` |

Data lengkap (1 admin, 3 dosen, 30 mahasiswa, 5 mata kuliah, tugas, submission, dan nilai):

```bash
php artisan migrate:fresh --seed
```

Hanya tiga akun demo (aman dijalankan berulang kali, tidak membuat duplikat):

```bash
php artisan db:seed --class=DemoAccountSeeder --force
```

---

## Menjalankan Test

Test memakai SQLite in-memory (diatur di `phpunit.xml`), sehingga **tidak menyentuh database pengembangan**.

```bash
php artisan test
```

Pengujian otorisasi lewat HTTP (aplikasi harus sedang berjalan):

```bash
chmod +x scripts/test-authz.sh
./scripts/test-authz.sh
```


---

## Queue & Notifikasi (Minggu 10)

Notifikasi in-app dikirim **di latar belakang lewat queue database**, supaya dosen tidak menunggu saat menyimpan tugas atau nilai.

| Peristiwa | Event | Listener (`ShouldQueue`) | Notification (saluran `database`) | Penerima |
|-----------|-------|--------------------------|-----------------------------------|----------|
| Tugas baru dipublikasikan | `AssignmentPublished` | `SendAssignmentNotification` | `NewAssignmentNotification` | Seluruh mahasiswa di mata kuliah tersebut |
| Nilai diberikan/diperbarui | `GradeGiven` | `SendGradeNotification` | `GradeGivenNotification` | Mahasiswa pemilik submission |

Payload notifikasi hanya berisi ID, judul, dan tautan. **Nilai dan isi feedback sengaja tidak disimpan** di tabel `notifications` karena kolom `data` tidak terenkripsi.

### Konfigurasi

1. Pastikan `.env` memakai queue database:

   ```env
   QUEUE_CONNECTION=database
   ```

2. Pastikan tabel `jobs`, `job_batches`, `failed_jobs`, dan `notifications` sudah ada:

   ```bash
   php artisan migrate
   ```

   > Jangan jalankan `make:queue-table` atau `make:queue-failed-table`. Tabelnya sudah dibuat oleh migrasi bawaan, dan perintah itu justru membuat migrasi ganda yang membuat `migrate` gagal.

### Menjalankan Worker

Buka **terminal terpisah** dari `php artisan serve` dan biarkan tetap berjalan selama aplikasi dipakai:

```bash
php artisan queue:work --tries=3
```

Atau jalankan server, queue, log, dan Vite sekaligus lewat satu perintah:

```bash
composer run dev
```

> **Peringatan:** jika `QUEUE_CONNECTION=database` tetapi worker **tidak berjalan**, job hanya menumpuk di tabel `jobs` dan **tidak ada notifikasi yang terkirim**. Tidak ada pesan error dan aplikasi tampak normal. Ini bukan bug, memang begitu cara kerja queue. Saat notifikasi "tidak sampai", periksa worker lebih dulu.

Setelah mengubah kode Listener atau Notification, **restart worker**, karena worker membaca kode sekali saat dijalankan:

```bash
php artisan queue:restart
```

### Memantau dan Memperbaiki Job

| Tujuan | Perintah |
|--------|----------|
| Melihat job yang masih menunggu | `php artisan tinker --execute="echo DB::table('jobs')->count();"` |
| Melihat job yang gagal | `php artisan queue:failed` |
| Mengulang semua job gagal | `php artisan queue:retry all` |
| Mengulang satu job gagal | `php artisan queue:retry <uuid>` |
| Menghapus semua catatan job gagal | `php artisan queue:flush` |
| Melihat log kegagalan | `storage/logs/laravel.log` |

Setiap Listener dan Notification memakai `tries = 3` dan `backoff = 10` detik. Job yang tetap gagal setelah tiga percobaan dicatat di tabel `failed_jobs`, dan method `failed()` menulis pesan ke log.

### Alur Uji Manual

1. Jalankan `php artisan serve`. **Jangan** jalankan worker dulu.
2. Login sebagai dosen, lalu buat tugas dengan status **Publikasi**.
3. Cek tabel `jobs`: ada baris baru, sedangkan tabel `notifications` masih kosong.
4. Jalankan `php artisan queue:work`. Keluarannya menampilkan job diproses (`DONE`).
5. Cek tabel `notifications`: ada satu baris per mahasiswa terdaftar. Beri nilai pada sebuah submission untuk mencoba peristiwa kedua.

### Mode `sync` (hanya untuk pengembangan/test)

`QUEUE_CONNECTION=sync` menjalankan job langsung di dalam request tanpa worker. Mode ini dipakai oleh `phpunit.xml` agar test deterministik, tetapi **jangan dipakai di produksi** karena request kembali lambat dan manfaat queue hilang.

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

