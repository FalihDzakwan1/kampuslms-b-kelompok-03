### Nama: Fatika Rizki Syahada
#### NIM: 10241030

## A. READ — Ukur aplikasi Anda sendiri (40 menit)

Sebelum melakukan pengukuran, saya memastikan database di laptop lokal sudah diisi seeder lengkap lewat perintah `php artisan migrate:fresh --seed` (memuat 15 mata kuliah, 30 mahasiswa, materi, tugas, dan nilai). Pengukuran dilakukan menggunakan **Laravel Debugbar** pada browser:

| Halaman | Jumlah Query | Waktu (ms) | Memori |
|---|:---:|:---:|:---:|
| **Daftar mata kuliah** (`/dosen/courses`) | 36 Query | 92 ms | 720 KB |
| **Detail mata kuliah** (`/courses/{id}`) | 28 Query | 84 ms | 820 KB |
| **Daftar tugas** (`/courses/{id}/assignments`) | 14 Query | 45 ms | 260 KB |
| **Daftar pengumpulan (dosen)** (`/submissions`) | 22 Query | 74 ms | 420 KB |
| **Rekap nilai** (`/courses/{id}` tab rekap) | 28 Query | 115 ms | 1.1 MB |
| **Dashboard mahasiswa** (`/dashboard`) | 3 Query | 185 ms | 2.1 MB |
| **Dashboard dosen** (`/dashboard`) | 3 Query | 180 ms | 2.1 MB |

### 1. Urutkan dari yang terburuk

Dari hasil pencatatan Debugbar, urutan halaman dari yang performanya paling lambat dan memakan beban terbesar adalah:

1. **Rekap Nilai (`GET /courses/{id}` tab rekap):** Halaman ini paling lambat (115 ms) dan paling boros memori (1.1 MB). Sistem harus mengambil seluruh data tugas, submission dari seluruh mahasiswa di kelas, serta nilai di tabel `grades`.
2. **Daftar Mata Kuliah (`GET /dosen/courses`):** Halaman ini menghasilkan query terbanyak yaitu 36 query (92 ms) akibat masalah N+1 query: mengambil data dosen dan menghitung jumlah materi serta tugas per baris kartu kelas.
3. **Detail Mata Kuliah (`GET /courses/{id}`):** Menghasilkan 28 query dalam 84 ms karena harus memuat berkas materi, pengunggah, dan daftar tugas aktif.
4. **Daftar Pengumpulan Tugas (`GET /submissions`):** Menghasilkan 22 query (74 ms) karena looping nama mahasiswa dan nilai pada setiap baris jawaban tugas.
5. **Daftar Tugas (`GET /courses/{id}/assignments`):** Menghasilkan 14 query (45 ms).
6. **Dashboard Mahasiswa & Dosen (`GET /dashboard`):** Paling sedikit query (hanya 3 query), tetapi waktu eksekusinya mencapai ~180 ms karena memproses sesi autentikasi awal.

### 2. Temukan query yang berulang dengan pola sama (N+1 Query)

Ketika memeriksa tab **Queries** di Debugbar pada halaman yang lambat, saya menemukan query yang dieksekusi berulang-ulang dengan pola identik:

Pada halaman **Rekap Nilai**, query berulang terjadi saat sistem mengambil nilai untuk setiap submission mahasiswa:
```sql
SELECT * FROM `grades` WHERE `submission_id` = 1 LIMIT 1;
SELECT * FROM `grades` WHERE `submission_id` = 2 LIMIT 1;
SELECT * FROM `grades` WHERE `submission_id` = 3 LIMIT 1;
```

Sedangkan pada halaman **Daftar Mata Kuliah**, perulangan terjadi di tampilan Blade saat menghitung materi dan tugas:
```sql
SELECT COUNT(*) FROM `materials` WHERE `course_id` = 1;
SELECT COUNT(*) FROM `assignments` WHERE `course_id` = 1;
SELECT COUNT(*) FROM `materials` WHERE `course_id` = 2;
SELECT COUNT(*) FROM `assignments` WHERE `course_id` = 2;
```
Pola ini merupakan masalah N+1: 1 query untuk mengambil daftar kelas, diikuti puluhan query tambahan hanya untuk menghitung data relasi di setiap baris.

### 3. Salin satu query terlambat, jalankan EXPLAIN di database

Saya menyalin salah satu query pencarian pengumpulan tugas mahasiswa berdasarkan `assignment_id` dan `user_id`:
```sql
EXPLAIN SELECT * FROM `submissions` WHERE `assignment_id` = 1 AND `user_id` = 5;
```

Hasil pengamatan pada kolom `EXPLAIN`:
* **Sebelum ada indeks:**
  * **`type`:** `ALL` (artinya database memindai seluruh baris tabel secara penuh alias *Full Table Scan*).
  * **`rows`:** database harus memeriksa semua baris di tabel `submissions`.
  * **`key`:** `NULL` (tidak ada indeks yang digunakan).
* **Setelah dipasang indeks komposit `(assignment_id, user_id)`:**
  * **`type`:** berubah menjadi `ref`.
  * **`rows`:** turun drastis menjadi hanya `1` baris.
  * **`key`:** menggunakan `submissions_assignment_id_user_id_index`.

Dengan adanya indeks, database tidak perlu lagi menelusuri isi tabel baris demi baris, melainkan langsung menuju data yang dicari sehingga kueri menjadi jauh lebih cepat.

---

## B. BREAK — Tujuh percobaan (45 menit)

### 1. Pasang Model::preventLazyLoading(), jalankan seluruh halaman

Untuk mendeteksi N+1 query tersembunyi, saya menambahkan baris berikut di `app/Providers/AppServiceProvider.php`:

```php
Model::preventLazyLoading(! app()->isProduction());
```

Begitu fitur ini aktif di lingkungan lokal, beberapa halaman seperti daftar mata kuliah dan rekap nilai langsung memunculkan layar merah error `LazyLoadingViolationException`. 

Terdapat **3 titik N+1 tersembunyi** yang langsung tertangkap, yaitu pemanggilan relasi `$course->lecturer->name`, penghitungan `$course->materials()->count()`, dan relasi nilai `$submission->grade`. Fitur ini sangat efektif memaksa developer memperbaiki eager loading di controller sebelum kode dirilis ke produksi.

### 2. Hapus semua with() dari satu controller, ukur ulang

Pada `CourseController`, saya mencoba menghapus pemanggilan eager loading `with('lecturer')` untuk melihat dampaknya pada Debugbar.

Hasilnya, jumlah kueri langsung melonjak dari **6 query menjadi 21 query**, dan waktu respons meningkat dari 24 ms menjadi 78 ms. Hal ini membuktikan bahwa tanpa eager loading, Laravel terpaksa mengirim query SQL baru ke database pada setiap baris perulangan data yang tampil di layar.

### 3. Ganti withCount('students') menjadi count($course->students), ukur memori

Saya menguji perbedaan konsumsi memori antara menghitung langsung di database dengan menghitung koleksi model di PHP:

* Saat menggunakan `count($course->students)`, memori melonjak dari **650 KB menjadi 2.4 MB**. Sintaks ini menarik seluruh objek data mahasiswa beserta atribut kolomnya ke dalam memori RAM laptop, baru kemudian dihitung jumlahnya oleh PHP.
* Saat dikembalikan menggunakan `withCount('students')`, memori tetap hemat di angka **654 KB**. Hal ini karena penghitungan dilakukan langsung oleh mesin database via `SELECT COUNT(*)`, dan Laravel hanya menerima satu angka ringkas tanpa memuat objek model ke RAM.

### 4. Hapus index dari assignments.course_id, jalankan EXPLAIN

Saya mencoba menghapus foreign key index pada kolom `course_id` di tabel `assignments`, lalu menjalankan perintah `EXPLAIN` kembali:

```sql
EXPLAIN SELECT * FROM assignments WHERE course_id = 1;
```

Hasilnya, nilai kolom **`type` berubah menjadi `ALL`** dan kolom `possible_keys` bernilai `NULL`. Tanpa indeks, database kehilangan petunjuk cepat dan terpaksa memeriksa seluruh baris tabel dari awal sampai akhir (*Full Table Scan*). Pada tabel yang sudah memiliki ribuan tugas, ketiadaan indeks ini akan membuat query terasa sangat berat.

### 5. Naikkan seeder menjadi 300 mahasiswa dan 1000 submission, buka rekap nilai

Saya menaikkan volume seeder pengujian menjadi 300 akun mahasiswa dan lebih dari 1.000 data pengumpulan tugas, lalu membuka halaman rekap nilai yang belum dioptimalkan.

Halaman mengalami perlambatan drastis hingga membutuhkan waktu loading **lebih dari 1.8 detik** dan memakan memori besar. Masalah N+1 yang awalnya terlihat sepele pada data sedikit menjadi beban fatal ketika volume data meningkat. Namun setelah kode dioptimasi menggunakan eager loading bertingkat `with(['assignments.submissions.grade'])`, waktu loading kembali turun stabil ke kisaran **~45 ms**.

### 6. Jalankan config:cache, lalu panggil env('APP_NAME') dari sebuah controller

Saya menjalankan perintah optimasi produksi `php artisan config:cache`, kemudian mencoba memanggil `env('APP_NAME')` di dalam salah satu method controller.

Fungsi tersebut mengembalikan nilai **`null`**. Penyebabnya adalah setelah konfigurasi di-cache, Laravel menggabungkan seluruh konfigurasi ke dalam satu berkas cache di `bootstrap/cache/config.php` dan tidak lagi membaca berkas `.env`. Oleh karena itu, di luar berkas `config/*.php`, pemanggilan konfigurasi wajib menggunakan `config('app.name')` dan tidak boleh menggunakan `env()`.

### 7. Pasang cache 10 menit pada nilai mahasiswa, ubah nilainya, muat ulang

Saya mencoba memasang cache selama 10 menit menggunakan `Cache::remember()` pada query pengambilan nilai mahasiswa, kemudian dosen memperbarui nilai tugas mahasiswa tersebut di database.

Ketika halaman mahasiswa di-refresh, nilainya tidak berubah dan masih menampilkan nilai lama (*stale data*) karena sistem masih menyajikan data yang tersimpan di dalam cache. Percobaan ini menegaskan bahwa tidak semua data layak disimpan di cache; data dinamis yang memerlukan akurasi tinggi seperti nilai akademik tidak boleh di-cache secara statis tanpa mekanisme pembersihan cache otomatis (*cache invalidation*).
