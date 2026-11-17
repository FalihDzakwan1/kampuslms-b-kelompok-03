#### Nama : Falih Dzakwan
#### NIM  : 10241028

---

# MINGGU 11 — Pengukuran Performa, N+1 Query, Indeks Database, dan Caching

Catatan individu Minggu 11 ini merangkum pengukuran performa halaman KampusLMS (**READ**) serta tujuh eksperimen penurunan dan optimasi performa aplikasi (**BREAK**) pada repositori `kampuslms-b-kelompok-03`.

---

## READ — Ukur Aplikasi Anda Sendiri (40 Menit)

Pengukuran dilakukan menggunakan data seeder lengkap (`php artisan migrate:fresh --seed`) dan **Laravel Debugbar** sebelum seluruh perbaikan N+1 dan caching diterapkan secara penuh.

### Ringkasan Pengukuran Awal Halaman Utama

| Halaman | Endpoint | Jumlah Query | Waktu (ms) | Memori |
|---|---|---:|:---:|:---:|
| **Daftar mata kuliah** | `GET /courses` | ~36 Query | ~92 ms | ~720 KB |
| **Detail mata kuliah** | `GET /courses/{course}` | ~28 Query | ~84 ms | ~820 KB |
| **Rekap nilai** | `GET /courses/{course}` (tab rekap) | ~28 Query | ~115 ms | ~1.1 MB |
| **Daftar pengumpulan (dosen/mhs)** | `GET /submissions` | ~22 Query | ~74 ms | ~420 KB |
| **Daftar tugas** | `GET /courses/{course}/assignments` | ~14 Query | ~45 ms | ~260 KB |
| **Dashboard mahasiswa / dosen** | `GET /dashboard` | 3 Query | ~180 ms | ~2.1 MB |

### 1. Urutkan dari yang Terburuk

Berdasarkan beban waktu eksekusi, memori, dan jumlah kueri SQL:
1. **Rekap Nilai (`CourseController@show`):** Waktu proses paling lama (~115 ms) dan konsumsi memori besar (~1.1 MB) karena memuat seluruh tugas, submission mahasiswa, dan nilai `grades` sekaligus.
2. **Daftar Mata Kuliah (`CourseController@index`):** Jumlah kueri SQL terbanyak (~36 query) akibat N+1 query saat mengambil nama dosen serta menghitung jumlah materi dan tugas di setiap baris kartu mata kuliah.
3. **Detail Mata Kuliah (`CourseController@show`):** ~28 query untuk memuat daftar materi, pengunggah materi, dan tugas aktif.
4. **Daftar Pengumpulan Tugas (`SubmissionController@index`):** ~22 query akibat perulangan relasi tugas, mata kuliah, dan nilai pada setiap baris pengumpulan.
5. **Daftar Tugas (`AssignmentController@index`):** ~14 query.
6. **Dashboard (`DashboardController@index`):** Paling sedikit dari sisi query database (3 query setelah dioptimasi dengan `DashboardCacheService`), dengan waktu utama dihabiskan pada inisialisasi sesi dan rendering view.

---

### 2. Temukan Query yang Berulang dengan Pola Sama (N+1 Query)

Pada tab **Queries** Laravel Debugbar di halaman **Daftar Mata Kuliah** dan **Rekap Nilai** sebelum dioptimasi, ditemukan query yang dipanggil berulang dengan pola identik:

* **Pola N+1 pada Daftar Mata Kuliah (`CourseController@index`):**
  ```sql
  SELECT * FROM `users` WHERE `id` = 2 LIMIT 1;
  SELECT COUNT(*) FROM `materials` WHERE `course_id` = 1;
  SELECT COUNT(*) FROM `assignments` WHERE `course_id` = 1;
  SELECT COUNT(*) FROM `materials` WHERE `course_id` = 2;
  SELECT COUNT(*) FROM `assignments` WHERE `course_id` = 2;
  ```
  *Solusi yang diterapkan di `CourseController.php:27-29`:* Menggunakan `->with('lecturer')->withCount(['materials', 'assignments'])` serta `->paginate(15)` agar jumlah kueri menjadi konstan dan tidak bertambah seiring jumlah mata kuliah.

* **Pola N+1 pada Daftar Tugas & Materi:**  
  Pada komitmen `89573e0` (*"fix(perf): eager load relasi course pada daftar tugas dan materi"*), saya juga menambahkan `->with('course')` pada query daftar tugas dan `->with(['course', 'uploader'])` pada `MaterialController.php:24-28` karena evaluasi directive `@can('update', $material)` di Blade memanggil `$material->course->lecturer_id`.

---

### 3. Salin Satu Query Terlambat, Jalankan `EXPLAIN` di Database

Pengujian `EXPLAIN` dijalankan pada query pencarian notifikasi belum dibaca (yang dipanggil terus-menerus oleh navbar polling) dan pencarian materi berdasarkan `course_id` serta `created_at` sesuai migrasi indeks `database/migrations/2026_11_01_125818_add_performance_indexes.php`:

```sql
EXPLAIN SELECT * FROM `notifications`
WHERE `notifiable_id` = 5
  AND `notifiable_type` = 'App\\Models\\User'
  AND `read_at` IS NULL;
```

* **Sebelum ditambahkan indeks komposit (`idx_notifications_notifiable_read`):**
  * `type`: `ALL` (atau hanya indeks parsial `notifiable_id` tanpa `read_at`, memindai banyak baris).
  * `rows`: memeriksa seluruh/banyak baris notifikasi yang sudah pernah dibaca.
* **Setelah ditambahkan indeks komposit `['notifiable_id', 'notifiable_type', 'read_at']` (`idx_notifications_notifiable_read`):**
  * `type`: `ref`
  * `key`: `idx_notifications_notifiable_read`
  * `rows`: turun drastis hanya pada baris yang belum dibaca milik pengguna terkait saja.

---

## BREAK — Tujuh Percobaan (45 Menit)

| # | Yang Dicoba | Yang Diamati | Penjelasan Teknis & Analisis Masalah |
|---|---|---|---|
| **1** | Aktifkan `Model::preventLazyLoading()` di `AppServiceProvider`, lalu buka seluruh halaman aplikasi. | Muncul error merah **`LazyLoadingViolationException`** pada halaman yang lupa melakukan *eager loading*. | **Deteksi Dini N+1 Tersembunyi:** `preventLazyLoading(! app()->isProduction())` memaksa developer menemukan titik lazy loading tersembunyi (seperti akses `$material->course` di dalam Policy `@can` Blade atau `$submission->grade`) di lingkungan lokal sebelum naik ke produksi. |
| **2** | Hapus `with('lecturer')` dan `withCount(['materials', 'assignments'])` dari `CourseController@index`, lalu ukur ulang di Debugbar. | Jumlah query melonjak drastis dari di bawah 10 query menjadi puluhan query berulang (1 query utama + $N$ query relasi dosen/hitung materi/hitung tugas), dan waktu loading meningkat berkali lipat. | **Pentingnya Eager Loading:** Tanpa `with()`, setiap baris iterasi `@foreach` di Blade akan mengeksekusi query database terpisah. Pada halaman dengan 15–30 mata kuliah, overhead round-trip SQL langsung terasa. |
| **3** | Ganti `withCount('students')` menjadi `count($course->students)`, lalu ukur penggunaan memori di Debugbar. | Konsumsi memori PHP melonjak tajam (dari ratusan KB menjadi beberapa MB). | **Menghitung di Database vs Memuat ke RAM:** `count($course->students)` menginstruksikan Laravel menarik **seluruh record dan kolom** mahasiswa dari database ke objek Collection di memori PHP hanya untuk dihitung panjang array-nya. Sebaliknya, `withCount('students')` menjalankan `SELECT COUNT(*)` di engine SQL dan hanya mengirim 1 angka integer ke PHP. |
| **4** | Hapus indeks dari `assignments.course_id` (atau `idx_materials_course_created`), lalu jalankan `EXPLAIN`. | Kolom **`type` berubah menjadi `ALL`** dan `key` menjadi `NULL`. | **Full Table Scan:** Tanpa indeks, database harus membaca tabel secara berurutan dari baris pertama hingga terakhir setiap kali memfilter tugas/materi milik sebuah mata kuliah. Pada data ribuan baris, performa query akan anjlok drastis. |
| **5** | Naikkan seeder menjadi 300 mahasiswa dan 1.000 submission, lalu buka halaman rekap nilai & daftar submission tanpa paginasi. | Halaman melambat signifikan (waktu respon tembus di atas 1 detik) dan penggunaan memori meningkat tajam karena ribuan objek Eloquent dimuat sekaligus. | **Skalabilitas Data Nyata:** Query `->get()` tanpa batas dan N+1 yang tampak cepat saat data hanya 5–10 baris akan menjadi *bottleneck* parah di dunia nyata. Solusi kami di `SubmissionController@index:26-42` adalah memakai **`->paginate(15)`** serta memindahkan hitungan total & rata-rata nilai (`totalCollected`, `totalGraded`, `avgScore`) ke **query agregasi database murni**. |
| **6** | Jalankan `php artisan config:cache`, lalu panggil `env('APP_NAME')` langsung di dalam controller. | Pemanggilan `env('APP_NAME')` mengembalikan nilai **`null`**. | **Aturan Ketat `config()` vs `env()`:** Setelah `config:cache` dieksekusi di produksi (`docker/start.sh:27`), Laravel tidak lagi membaca file `.env` pada tiap request demi kecepatan dan hanya memuat `bootstrap/cache/config.php`. Karena itu, kode aplikasi di luar folder `config/` **wajib** memanggil `config('app.name')`, bukan `env()`. |
| **7** | Pasang cache `Cache::remember()` selama 10 menit pada data nilai mahasiswa, ubah nilainya sebagai dosen, lalu muat ulang halaman mahasiswa. | Nilai mahasiswa di layar tidak berubah dan masih menampilkan nilai lama sampai masa cache 10 menit habis. | **Stale Data (Data Basi) vs Cache Invalidation:** Data transaksional kritis seperti nilai mahasiswa tidak boleh di-cache statis tanpa mekanisme penghapusan otomatis (*invalidation*). Karena itu pada `App\Services\DashboardCacheService`, caching (`CACHE_TTL_SECONDS = 3600`) hanya dipasang untuk metrik ringkasan global dashboard dan disertai helper `clearGlobalStats()` setiap kali data kelas/materi/tugas berubah. |

---

## Kesimpulan

Optimasi performa aplikasi Laravel tidak hanya mengandalkan kecepatan perangkat keras, melainkan kombinasi dari empat pilar utama: eliminasi N+1 query dengan `with()` / `withCount()` dan paginasi, agregasi perhitungan langsung di SQL, pemasangan *database index* yang tepat untuk kolom `WHERE`/`ORDER BY`, serta penggunaan caching yang disiplin disertai *cache invalidation* agar tidak menyajikan data basi (*stale data*).
