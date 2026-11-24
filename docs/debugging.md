# Dokumentasi Investigasi & Debugging Sistematis
**KampusLMS — Kelompok 03 (Kelas B)**  
*Institut Teknologi Kalimantan*

Dokumen ini mencatat investigasi tiga *bug* nyata yang dialami Kelompok 03 selama pengembangan KampusLMS. Setiap insiden ditangani menggunakan metode debugging sistematis (**Reproduksi → Persempit → Rumuskan Hipotesis → Uji & Perbaiki → Tulis Automated Test**) agar regresi serupa tidak terulang.

---

## 1. Bug 1: MySQL Error 1553 Foreign Key Index Lock saat `migrate:refresh` di CI

### A. Gejala (*Symptoms*)
Pada workflow CI GitHub Actions (`.github/workflows/ci.yml`), step `Migrate Refresh` (`php artisan migrate:refresh`) gagal pada environment MySQL 8 dengan galat:
```text
SQLSTATE[HY000]: General error: 1553 Cannot drop index 'idx_materials_course_created':
needed in a foreign key constraint (Connection: mysql, SQL: alter table `materials` drop index `idx_materials_course_created`)
```
Sementara pengujian lokal menggunakan SQLite in-memory tidak memunculkan galat tersebut.

### B. Metode Investigasi & *Root Cause Analysis*
1. **Reproduksi:** Menjalankan `php artisan migrate:refresh` pada koneksi MySQL mereproduksi kegagalan tepat saat rollback tabel `materials`.
2. **Persempit:** Baris aplikasi pertama pada stack trace menunjuk ke berkas migrasi `database/migrations/2026_11_01_125818_add_performance_indexes.php` pada method `down()`.
3. **Hipotesis & Analisis:**
   - Pada `up()`, dibuat composite index `['course_id', 'created_at']` dengan nama `idx_materials_course_created`.
   - Di InnoDB (MySQL), karena kolom pertama index tersebut adalah `course_id`, MySQL mengikat constraint foreign key `materials_course_id_foreign` ke index gabungan ini.
   - Saat rollback hanya memanggil `$table->dropIndex('idx_materials_course_created')`, InnoDB menolak penghapusan karena index masih menopang foreign key yang aktif.

### C. Solusi
Pada method `down()` di `database/migrations/2026_11_01_125818_add_performance_indexes.php`, driver non-SQLite diperbaiki agar melepas foreign key terlebih dahulu, menghapus index komposit, lalu memasang kembali constraint foreign key:
```php
if (DB::getDriverName() === 'sqlite') {
    $table->dropIndex('idx_materials_course_created');
} else {
    $table->dropForeign(['course_id']);
    $table->dropIndex('idx_materials_course_created');
    $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
}
```

### D. Automated Test & Penjaga Regresi
- Step `Migrate Refresh` (`php artisan migrate:refresh`) pada `.github/workflows/ci.yml` di atas service MySQL 8.
- Trait `RefreshDatabase` pada seluruh feature test di `tests/Feature/` yang terus memverifikasi skema database bersih dan migrasi bersifat *reversible*.

---

## 2. Bug 2: Potensi Celah *Open Redirect* pada `NotificationController@open`

### A. Gejala (*Symptoms*)
Pada proses peer review PR #59 oleh reviewer luar (`calvinwinata21`), ditemukan bahwa handler klik notifikasi di `app/Http/Controllers/NotificationController.php` pada method `open()` meneruskan `$notification->data['url']` langsung ke fungsi `redirect($targetUrl)` tanpa memvalidasi kesesuaian domain/host maupun jenis path. Jika nilai payload di database mengarah ke domain eksternal, pengguna berisiko diarahkan keluar menuju situs phishing.

### B. Metode Investigasi & *Root Cause Analysis*
1. **Reproduksi:** Membuat record notifikasi pada `DatabaseNotification` dengan nilai `data['url']` berisi URL eksternal (`https://evil-phishing-site.test/login`), lalu mengakses route `notifications.open`. Hasil sebelumnya mengarahkan respons HTTP 302 langsung ke domain eksternal tersebut.
2. **Persempit:** Inspeksi pada `app/Http/Controllers/NotificationController.php` menunjukkan bahwa meskipun kepemilikan notifikasi sudah diverifikasi melalui `Gate::authorize('update', $notification)`, tujuan pengalihan belum disaring untuk memastikan URL tetap berada di origin internal aplikasi.
3. **Hipotesis:** Menambahkan whitelist untuk path relatif internal (dimulai dengan `/` tunggal) dan URL absolut dengan host yang identik dengan `config('app.url')` / `$request->getHost()` akan menetralkan URL eksternal dan mengalihkannya ke `route('dashboard')`.

### C. Solusi
Pada `app/Http/Controllers/NotificationController.php` (`open()`), diterapkan pemeriksaan keamanan URL tujuan sebelum `return redirect($targetUrl)`:
- Mengizinkan path relatif yang diawali `'/'` dan bukan `'//'` (mencegah *protocol-relative URL*).
- Jika berupa URL penuh (`FILTER_VALIDATE_URL`), host tujuan (`parse_url($targetUrl, PHP_URL_HOST)`) harus cocok dengan `config('app.url')` atau `$request->getHost()`.
- Jika tidak lolos validasi (`!$isSafe`), tujuan fallback otomatis ke `route('dashboard')`.

### D. Automated Test & Penjaga Regresi
Tiga feature test ditambahkan pada `tests/Feature/NotificationQueueTest.php`:
- `test_buka_notifikasi_menandai_dibaca_dan_redirect_ke_url_internal` — Memastikan URL internal relatif (`/mahasiswa/courses`) diarahkan dengan benar dan `read_at` terisi.
- `test_buka_notifikasi_mencegah_celah_open_redirect_ke_domain_eksternal` — Memastikan payload `https://evil-phishing-site.test/login` dinetralkan dan dialihkan ke `route('dashboard')`.
- `test_buka_notifikasi_milik_orang_lain_ditolak_dengan_403` — Memastikan notifikasi milik pengguna lain tetap ditolak (HTTP 403 Forbidden) dan `read_at` tetap `null`.

---

## 3. Bug 3: *Lazy Loading Violation* & N+1 Query pada Katalog Mata Kuliah dan Daftar Materi

### A. Gejala (*Symptoms*)
Saat `Model::preventLazyLoading(! $this->app->isProduction())` diaktifkan di `app/Providers/AppServiceProvider.php`, akses ke halaman daftar materi maupun katalog mata kuliah di lingkungan non-produksi memicu exception `Illuminate\Database\LazyLoadingViolationException` ketika Blade membaca properti relasi model di dalam loop tabel.

### B. Metode Investigasi & *Root Cause Analysis*
1. **Reproduksi:** Mengakses `MaterialController@index` atau `CourseController@index` dengan data relasi `uploader`, `course`, dan `lecturer` yang belum di-eager-load.
2. **Persempit:** Stack trace mengarah ke template Blade `resources/views/materials/index.blade.php` (pada pemanggilan `$material->uploader->name` di kolom pengunggah dan direktif `@can('update', $material)` yang memicu `MaterialPolicy` untuk memeriksa `$material->course->lecturer_id`), serta ke pemanggilan `$course->lecturer->name` pada tampilan katalog.
3. **Hipotesis:** Karena query pada controller belum menyertakan `with(...)`, setiap iterasi `@foreach` memicu query tambahan ke tabel `users` dan `courses` (N+1 query) yang langsung diblokir oleh `Model::preventLazyLoading()`.

### C. Solusi
Menerapkan eager loading dan agregasi pada controller terkait:
- `app/Http/Controllers/MaterialController.php` (`index()`): memuat relasi `->with(['uploader', 'course'])`.
- `app/Http/Controllers/CourseController.php` (`index()`): memuat `->with('lecturer')->withCount(['materials', 'assignments'])`.

### D. Automated Test & Penjaga Regresi
Dijaga oleh test suite pada `tests/Feature/PerformanceOptimizationTest.php`:
- `test_model_prevent_lazy_loading_aktif_di_lingkungan_non_produksi` — Memastikan proteksi lazy loading aktif di lingkungan testing/lokal.
- `test_seluruh_halaman_utama_berjalan_tanpa_melanggar_lazy_loading` — Menguji seluruh endpoint utama (dashboard, courses, materials, assignments, submissions, users, notifications) kembali 200 OK tanpa `LazyLoadingViolationException`.
- `test_courses_index_bebas_dari_masalah_n_plus_satu_query` — Memverifikasi query pada katalog mata kuliah tidak bertambah secara linier ketika jumlah data meningkat.
