# Catatan Individu Minggu - 11 - Elsya Nur Aulia Handayani (10241026)

## 11.3 Read → Break → Fix → Build

---

### READ — Ukur aplikasi Anda sendiri (40 menit)

Sebelum mulai mengukur, saya memastikan database sudah diisi data seeder lengkap lewat perintah `php artisan migrate:fresh --seed` (berisi 15 mata kuliah, 30 mahasiswa, materi, tugas, dan pengumpulan nilai). Setelah itu, saya mengukur performa setiap halaman menggunakan Laravel Debugbar di browser:

| Halaman | Jumlah query | Waktu (ms) | Memori |
|---|:---:|:---:|:---:|
| **Daftar mata kuliah** | 36 Query | 92 ms | 720 KB |
| **Detail mata kuliah** | 28 Query | 84 ms | 820 KB |
| **Daftar tugas** | 14 Query | 45 ms | 260 KB |
| **Daftar pengumpulan (dosen)** | 22 Query | 74 ms | 420 KB |
| **Rekap nilai** | 28 Query | 115 ms | 1.1 MB |
| **Dashboard mahasiswa** | 3 Query | 185 ms | 2.1 MB |
| **Dashboard dosen** | 3 Query | 180 ms | 2.1 MB |

---

#### 1. Urutkan dari yang terburuk.

Setelah mencatat hasilnya satu per satu, saya mengurutkan halaman dari yang paling berat ke yang paling ringan:

1. **Rekap nilai (`GET /courses/{id}` tab rekap):** Ini halaman yang paling lambat dan paling berat secara performa (28 query, waktu 115 ms, memori 1.1 MB). Bebannya berat karena sistem harus mengambil data mata kuliah, lalu menarik daftar tugas, semua file submission mahasiswa, sampai nilai di tabel grades sekaligus.
2. **Daftar mata kuliah (`GET /dosen/courses`):** Halaman ini menghasilkan query paling banyak, yaitu 36 query dengan waktu 92 ms. Penyebabnya karena ada masalah N+1, di mana sistem mengambil nama dosen pengampu dan menghitung jumlah materi serta tugas satu per satu untuk setiap baris kartu mata kuliah.
3. **Detail mata kuliah (`GET /courses/{id}`):** Menghasilkan 28 query dengan waktu 84 ms karena harus memuat data materi perkuliahan, pengunggah materi, dan tugas yang aktif secara bersamaan.
4. **Daftar pengumpulan tugas (`GET /submissions`):** Menghasilkan 22 query dengan waktu 74 ms karena sistem melakukan perulangan untuk mengambil nama mahasiswa dan nilai pada setiap baris tugas yang dikumpulkan.
5. **Daftar tugas (`GET /courses/{id}/assignments`):** Menghasilkan 14 query dengan waktu 45 ms untuk menampilkan daftar tugas kuliah.
6. **Dashboard mahasiswa & dosen (`GET /dashboard`):** Paling ringan dari sisi database, cuma 3 query (~180 ms). Waktunya lebih banyak terpakai untuk membaca sesi login pengguna saja.

---

#### 2. Untuk halaman terburuk, buka Debugbar tab Queries. Temukan query yang berulang dengan pola sama. Itu N+1 Anda.

Ketika saya membuka tab **Queries** di Debugbar pada halaman rekap nilai dan daftar mata kuliah, saya melihat ada query yang dipanggil berulang-ulang dengan bentuk yang sama persis:

Pada halaman **Rekap Nilai**, query yang berulang terjadi saat sistem mencari nilai untuk setiap tugas mahasiswa:
```sql
SELECT * FROM `grades` WHERE `submission_id` = 1 LIMIT 1;
SELECT * FROM `grades` WHERE `submission_id` = 2 LIMIT 1;
SELECT * FROM `grades` WHERE `submission_id` = 3 LIMIT 1;
```
Query ini terus dipanggil berulang kali sebanyak data tugas dan mahasiswa yang ada di kelas tersebut.

Sedangkan pada halaman **Daftar Mata Kuliah**, query yang berulang terjadi saat menghitung jumlah materi dan tugas di tampilan Blade:
```sql
SELECT COUNT(*) FROM `materials` WHERE `course_id` = 1;
SELECT COUNT(*) FROM `assignments` WHERE `course_id` = 1;
SELECT COUNT(*) FROM `materials` WHERE `course_id` = 2;
SELECT COUNT(*) FROM `assignments` WHERE `course_id` = 2;
```
Pola berulang inilah yang disebut masalah N+1: 1 query untuk mengambil daftar kelasnya, lalu ada puluhan query tambahan cuma buat menghitung materi dan tugas per baris.

---

#### 3. Salin satu query terlambat, jalankan EXPLAIN di database. Catat kolom type dan rows.

Saya menyalin salah satu query pencarian pengumpulan tugas mahasiswa berdasarkan `assignment_id` dan `user_id`:
```sql
EXPLAIN SELECT * FROM `submissions` WHERE `assignment_id` = 1 AND `user_id` = 5;
```

Hasil yang saya amati:
* **Sebelum ada indeks:**
  * **`type`:** bernilai `ALL` (artinya database membaca seluruh baris tabel dari awal sampai akhir alias *Full Table Scan*).
  * **`rows`:** database harus memeriksa semua baris data di tabel `submissions`.
  * **`key`:** `NULL` (tidak ada indeks yang dipakai).
* **Setelah dipasang indeks komposit `(assignment_id, user_id)`:**
  * **`type`:** berubah menjadi `ref`.
  * **`rows`:** turun drastis menjadi cuma `1` baris.
  * **`key`:** menggunakan `submissions_assignment_id_user_id_index`.

Artinya, dengan adanya indeks, database tidak perlu lagi memeriksa isi tabel satu per satu dari atas ke bawah, tetapi langsung melompat ke baris data yang dicari sehingga prosesnya jauh lebih cepat.

---

### BREAK — Tujuh percobaan (45 menit)

Dokumentasi hasil eksperimen saat saya mencoba merusak dan menguji batas performa aplikasi:

| # | Yang dicoba | Yang harus Anda amati | Penjelasan Teknis & Analisis Masalah |
|---|---|---|---|
| **1** | Pasang `Model::preventLazyLoading()`, jalankan seluruh halaman | Berapa N+1 tersembunyi yang muncul? | Begitu `Model::preventLazyLoading()` diaktifkan di `AppServiceProvider`, halaman daftar mata kuliah dan rekap nilai langsung melempar error merah **`LazyLoadingViolationException`**. Di sini langsung ketahuan ada **3 titik N+1 tersembunyi**, yaitu saat tampilan Blade memanggil relasi `$course->lecturer->name`, `$course->materials()->count()`, dan `$submission->grade`. Fitur ini sangat berguna supaya relasi yang lupa di-*eager load* bisa langsung ketahuan saat koding di lokal sebelum aplikasinya dipakai orang banyak. |
| **2** | Hapus semua `with()` dari satu controller, ukur ulang | Bandingkan angkanya | Pada `CourseController`, saat `with('lecturer')` saya hapus, query langsung melonjak drastis dari **6 query menjadi 21 query**, dan waktu loading-nya naik dari 24 ms menjadi 78 ms. Ini membuktikan kalau eager loading dihapus, Laravel terpaksa mengirim query tambahan ke database untuk setiap baris data yang tampil di layar. |
| **3** | Ganti `withCount('students')` menjadi `count($course->students)`, ukur memori | Beda menghitung vs memuat | Saat memakai `count($course->students)`, penggunaan memori melonjak dari **650 KB jadi 2.4 MB**. Penyebabnya karena sintaks `$course->students` menarik **semua data mahasiswa beserta kolom-kolomnya ke memori RAM laptop**, baru dihitung jumlahnya oleh PHP. Sebaliknya, kalau pakai `withCount('students')`, yang menghitung langsung mesin database lewat query `COUNT(*)` dan yang dikirim ke PHP cuma 1 angka saja, jadi memorinya tetap sangat hemat. |
| **4** | Hapus index dari `assignments.course_id`, jalankan `EXPLAIN` | `type` berubah menjadi `ALL` | Pada hasil perintah `EXPLAIN`, kolom **`type` berubah menjadi `ALL`** dan kolom `possible_keys` jadi `NULL`. Tanpa indeks, database kehilangan jalan pintas dan terpaksa membaca seluruh isi tabel dari awal sampai akhir (*Full Table Scan*) setiap kali mencari tugas milik suatu mata kuliah. Kalau datanya sudah ribuan, database bakal langsung lambat. |
| **5** | Naikkan seeder menjadi 300 mahasiswa dan 1000 submission, buka rekap nilai | Masalah yang tadinya kecil jadi jelas | Begitu jumlah data dinaikkan jadi banyak, halaman rekap nilai yang query-nya belum rapi langsung lemot parah dan loading-nya memakan waktu **lebih dari 1.8 detik**. Masalah N+1 yang awalnya terlihat sepele di data sedikit (cuma beda sekian milidetik) bakal jadi masalah besar saat datanya sudah banyak. Tapi setelah diperbaiki pakai eager loading bertingkat, waktunya kembali cepat dan stabil di **~45 ms**. |
| **6** | Jalankan `config:cache`, lalu panggil `env('APP_NAME')` dari sebuah controller | Mengembalikan `null` — ingat baik-baik | Saat dipanggil, fungsi `env('APP_NAME')` di controller malah menghasilkan nilai **`null`**. Ini karena setelah `config:cache` dijalankan, Laravel menyatukan semua setelan ke dalam satu file cache dan **berhenti membaca file `.env`**. Jadi kalau kita mau memanggil konfigurasi di luar folder `config/`, wajib hukumnya pakai fungsi `config('app.name')`, bukan `env()`. |
| **7** | Pasang cache 10 menit pada nilai mahasiswa, ubah nilainya, muat ulang | Data basi yang merugikan pengguna | Terjadi masalah **data basi (*stale data*)**. Misalnya dosen baru saja mengubah nilai tugas mahasiswa dari 70 jadi 90 di database, tapi saat mahasiswa me-refresh halaman, nilainya masih tetap 70 karena website masih menampilkan data lama yang tersimpan di cache (karena cache baru basi setelah 10 menit). Ini membuktikan bahwa tidak semua data boleh di-cache, terutama data nilai yang sering berubah dan harus selalu akurat detik itu juga. |
