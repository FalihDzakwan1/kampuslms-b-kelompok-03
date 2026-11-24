### Nama : Falih Dzakwan

#### NIM : 10241028

---

# MINGGU 14 — Debugging dan Testing

Laporan praktikum Minggu 14 ini disusun berdasarkan modul pembelajaran ([06-modul-minggu-13-16.md](file:///c:/Users/ASUS/Documents/Mata%20Kuliah/Semester%206/Proweb/Modul/06-modul-minggu-13-16.md#L337)) serta melengkapi bagian **BUILD Minggu 14** bersama Kelompok 03 (*Indriani Anwar*, *Fatika Rizki Syahada*, dan *Elsya Nur Aulia Handayani*).

---

## BAGIAN 1: READ — Baca Kegagalan dan Jejak Sistem

### 1. Eksekusi `php artisan test` dan Jumlah Test
Pengujian seluruh suite aplikasi dijalankan melalui terminal pada direktori repositori `kampuslms-b-kelompok-03`:
```bash
php artisan test
```
* **Hasil Eksekusi Aktual:**
  * **Unit Test (`tests/Unit/`):** 1 test (`ExampleTest`)
  * **Feature Test (`tests/Feature/`):** 107 test (`AuthorizationTest` 22, `CriticalFlowsTest` 22, `DeploymentHardeningTest` 4, `ExampleTest` 1, `FileSizeDisplayTest` 5, `FlashMessageTest` 5, `MaterialAssignmentAccessTest` 21, `NotificationQueueTest` 14, `PaginationAndDashboardCacheTest` 4, `PerformanceOptimizationTest` 5 — total seluruh assertion: **453 assertions**)
  * **Total:** **108 passed (453 assertions)** — 100% lulus tanpa failure maupun deprecation warning.

### 2. Membaca Keluaran Kegagalan PHPUnit
Ketika sebuah assertion pada feature test tidak terpenuhi, PHPUnit menampilkan:
1. Header `FAILED <TestClass> > <test name>` beserta ringkasan perbedaan antara *Expected* dan *Actual* (misalnya kode status HTTP `403` vs `200`, atau target URL `assertRedirect`).
2. Baris penunjuk lokasi tepat pada berkas test (`at tests/Feature/<NamaTest>.php:<nomor_baris>`), disertai cuplikan kode dan tanda panah (`➜`) pada assertion yang gagal. Dengan begitu, diagnosa langsung tertuju ke baris berkas uji yang bermasalah tanpa harus menelusuri seluruh `vendor/`.

### 3. Penelusuran Stack Trace di `storage/logs/laravel.log`
Pada pemeriksaan historis berkas log aplikasi (`storage/logs/laravel.log` baris 493–520), ditemukan pencatatan exception nyata berikut:
```text
[previous exception] [object] (Illuminate\Database\LazyLoadingViolationException(code: 0):
Attempted to lazy load [course] on model [App\Models\Assignment] but lazy loading is disabled.
at .../vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php:610)
[stacktrace]
#0 .../vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php(569): Illuminate/Database/Eloquent/Model->handleLazyLoadingViolation('course')
#1 .../vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php(494): Illuminate/Database/Eloquent/Model->getRelationValue('course')
#2 .../vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php(2427): Illuminate/Database/Eloquent/Model->getAttribute('course')
#3 .../app/Policies/AssignmentPolicy.php(66): Illuminate/Database/Eloquent/Model->__get('course')
```
* **Baris Pertama yang Menyebut Kode di `app/`:** Frame **`#3 app/Policies/AssignmentPolicy.php(66)`**.
* **Interpretasi Teknis:** Frame `#0`–`#2` adalah mekanisme internal framework (`vendor/laravel/...`) yang melempar `LazyLoadingViolationException`. Titik pemicu di kode proyek ada pada frame `#3` saat `AssignmentPolicy` membaca relasi `$assignment->course` untuk mengecek `lecturer_id` pada halaman yang memanggil `@can('update', $assignment)` tanpa eager loading `course` sebelumnya.

### 4. Pemantauan Query SQL pada Halaman Rekap Nilai (`DB::listen()` / Query Log)
Pada `app/Providers/AppServiceProvider.php`, proteksi performa `Model::preventLazyLoading(! $this->app->isProduction())` aktif di lingkungan pengembangan/pengujian. Selain itu, pengukuran query menggunakan `DB::enableQueryLog()` (dan pola serupa `DB::listen()` untuk mencatat SQL ke `laravel.log`) pada halaman rekap nilai (`dosen.courses.show` dan `mahasiswa.courses.show`) diverifikasi dalam `PerformanceOptimizationTest::test_halaman_rekap_nilai_berjalan_di_bawah_15_query`:
* Controller melakukan eager loading pada relasi `lecturer`, `materials.uploader`, dan `assignments.submissions.grade` sehingga jumlah kueri tetap berada di bawah ambang **15 query** (dan halaman katalog `courses.index` diuji tidak melebihi **5 query** untuk 10 mata kuliah beserta count materi dan tugas).

---

## BAGIAN 2: BREAK — Tujuh Percobaan Kegagalan

Ringkasan tujuh percobaan pengujian sesuai panduan Modul 14.3 untuk membuktikan risiko konfigurasi pengujian yang keliru:

| # | Percobaan | Dampak yang Diamati | Pelajaran Teknis |
|:-:|:---|:---|:---|
| **1** | Menulis test yang hanya memeriksa `$this->get(...)->assertStatus(200)` lalu mengosongkan logika controller | **Test tetap hijau (false positive)** meskipun fitur dan datanya rusak | `assertStatus(200)` saja tidak membuktikan kebenaran respons; test harus memverifikasi konten (`assertSee`/`assertDontSee`) atau perubahan database (`assertDatabaseHas`). |
| **2** | Menghapus `Gate::authorize()` dari controller otorisasi lalu menjalankan suite test | Test jalur negatif di `AuthorizationTest` / `CriticalFlowsTest` menjadi **merah (gagal 403 expected, 200 received)** | Jika menghapus `Gate::authorize()` tidak membuat satu pun test merah, artinya endpoint tersebut belum terjaga otomatis terhadap celah IDOR/otorisasi. |
| **3** | Menghapus trait `RefreshDatabase` lalu menjalankan test suite dua kali | Data dummy test sebelumnya tertinggal dan memicu **error duplikasi/kontaminasi data antar test** | `RefreshDatabase` mengisolasi tiap test agar idempotent dan bersih sebelum skenario berikutnya berjalan. |
| **4** | Menulis test yang bergantung pada data seeder tertentu (`Course::find(1)`) di CI | **Gagal di CI** karena database test baru (`:memory:` atau DB kosong) tidak selalu identik dengan data lokal | Setiap test wajib membuat prasyarat datanya sendiri via Model Factory (`User::factory()`, `Course::factory()`). |
| **5** | Menghapus `Storage::fake('local')` pada test unggah berkas | Berkas dummy hasil test **benar-benar menumpuk di folder `storage/app/`** | `Storage::fake('local')` menjaga sistem berkas disk tetap bersih selama eksekusi test lokal maupun CI. |
| **6** | Merusak method `down()` pada salah satu berkas migrasi | **`RefreshDatabase` / `migrate:refresh` gagal** (seperti insiden MySQL Error 1553 pada `idx_materials_course_created`) | Migrasi wajib *reversible* agar reset database di pengujian maupun CI selalu berhasil. |
| **7** | Meninggalkan `dd()` pada controller lalu menjalankan test | Eksekusi PHP **terhenti mendadak**, menyebabkan request test berikutnya tidak mendapatkan respons HTTP valid | `dd()` dan `dump()` dilarang masuk PR; gunakan `Log::info()` agar pencatatan tetap masuk berkas log tanpa memutus respons. |

---

## BAGIAN 3: FIX — Kriteria Memperbaiki Test yang Menipu

Berdasarkan studi kasus 8 test yang menipu pada modul (`3 status-only`, `2 tanpa assertion bermakna`, `1 dependensi seeder`, `1 dependensi urutan test`, dan `1 cek "tidak 200"`):
1. **Test Status-Only (3 kasus):** Lengkapi `assertOk()` / `assertRedirect()` dengan verifikasi isi tampilan (`assertSee`, `assertDontSee`) atau autentikasi aktual (`assertAuthenticatedAs`).
2. **Test Tanpa Assertion Nyata (2 kasus):** Pastikan efek samping mutasi data diuji lewat `assertDatabaseHas()`, `assertDatabaseMissing()`, atau `Storage::disk('local')->assertExists()`.
3. **Test Bergantung Seeder (1 kasus):** Ganti pembacaan ID tetap dari seeder dengan pembuatan record independen via `User::factory()->create()` dan `Course::factory()->create()`.
4. **Test Bergantung Urutan Test Sebelumnya (1 kasus):** Jadikan tiap method test *self-contained* (membuat user, course, dan attachment di dalam method test itu sendiri dengan `RefreshDatabase`).
5. **Pengecekan "Tidak 200" pada Otorisasi (1 kasus):** Ganti pengecekan longgar dengan assertion eksplisit **`assertForbidden()` (HTTP 403)** agar error server 500 atau redirect tidak dianggap lulus.

---

## BAGIAN 4: BUILD — Rekap Pemenuhan Suite & Dokumentasi Kelompok 03

Seluruh komponen **BUILD Minggu 14** pada repositori `kampuslms-b-kelompok-03` diselesaikan secara kolaboratif oleh anggota Kelompok 03:

| Syarat BUILD Modul 14 | Implementasi & Bukti di Repositori | Kontributor / PR |
|---|---|---|
| **1. Minimal 20 Feature Test mencakup 8 alur kritis** | `tests/Feature/CriticalFlowsTest.php` memuat **22 feature test** (`test_01` s.d. `test_22`) yang menguji Autentikasi, CRUD MK (Admin sukses vs Dosen/Mhs 403), Enrollment, Unggah Materi, Pengumpulan Tugas (`allow_late=false`, non-terdaftar), Unduhan (non-pemilik 403), Penilaian, dan Notifikasi. Total seluruh aplikasi mencapai **108 tests (453 assertions)**. | Indriani Anwar (PR #102) & dilengkapi suite sebelumnya (`AuthorizationTest`, `MaterialAssignmentAccessTest`, `NotificationQueueTest`) |
| **2. Kolom "Test" pada `docs/keamanan.md`** | Seluruh **8 baris titik rawan IDOR/BOLA/Privilege Escalation** pada `docs/keamanan.md` dilengkapi kolom **`Test Pembuktian (Automated Feature Test)`** yang memetakan tiap endpoint ke test di `AuthorizationTest`, `MaterialAssignmentAccessTest`, dan `CriticalFlowsTest`. | Fatika Rizki Syahada (PR #103) |
| **3. `Storage::fake()` & `Notification::fake()`** | `Storage::fake('local')` diterapkan pada uji unggah/unduh berkas (`CriticalFlowsTest`, `MaterialAssignmentAccessTest`, `AuthorizationTest`), dan `Notification::fake()` (`assertSentTo` & `assertNotSentTo`) diterapkan pada `test_19` dan `test_20` di `CriticalFlowsTest.php`, disinkronkan pula ke `docs/testing.md`. | Elsya Nur Aulia Handayani (PR #104), Indriani Anwar (PR #102), & Falih Dzakwan (sinkronisasi `docs/testing.md`) |
| **4. CI menjalankan `php artisan test` dan hijau** | `.github/workflows/ci.yml` menjalankan `php artisan migrate:fresh --seed`, `php artisan migrate:refresh`, dan `php artisan test` di atas MySQL 8 dan lulus hijau. | Kelompok 03 |
| **5. `docs/debugging.md` (3 bug nyata kelompok)** | Berkas `docs/debugging.md` mendokumentasikan 3 bug nyata semester ini beserta gejala, akar masalah, perbaikan, dan automated test penjaganya: (1) MySQL Error 1553 saat `migrate:refresh`, (2) Celah *Open Redirect* di `NotificationController@open`, dan (3) *Lazy Loading Violation* & N+1 Query di katalog & materi. Ditambah penguatan `Gate::authorize('create', Course::class)` pada `CourseController@create` dan `CourseController@store`. | Falih Dzakwan |

---

## BAGIAN 5: CHECKPOINT MINGGU 14

### 1. Peragakan: Hapus satu fitur, tunjukkan test yang menjadi merah karenanya.
* **Contoh Fitur Keamanan:** Proteksi IDOR pada `app/Policies/SubmissionPolicy.php` method `view()` baris 40:
  ```php
  'mahasiswa' => $submission->user_id === $user->id,
  ```
* **Simulasi:** Jika syarat kepemilikan pada baris tersebut dihapus/diubah menjadi `true`, maka ketika `php artisan test --filter=test_15_unduhan_submission_ditolak_untuk_mahasiswa_bukan_pemilik` (atau `AuthorizationTest::test_mahasiswa_tidak_dapat_melihat_submission_mahasiswa_lain`) dijalankan, pengujian seketika **MERAH (FAIL)** karena sistem mengembalikan HTTP `200 OK` alih-alih `403 Forbidden` yang dituntut oleh `assertForbidden()`.

### 2. Kenapa test yang hanya `assertStatus(200)` sering tidak bermakna?
Karena kode status `200 OK` hanya menandakan HTTP response selesai tanpa unhandled exception. Controller yang logika query-nya terhapus (mengembalikan daftar kosong) atau halaman yang mengalami kebocoran data milik pengguna lain tetap bisa mengembalikan `200 OK`. Test yang bermakna harus memeriksa efek samping atau kontennya, misalnya `assertSee()`, `assertDontSee()`, `assertDatabaseHas()`, dan `assertForbidden()` pada skenario penolakan.

### 3. Kenapa test jalur ditolak lebih penting daripada test jalur sukses di aplikasi ini?
Alur sukses (*happy path*) mudah terlihat dan hampir selalu dicoba manual selama pengembangan. Sebaliknya, celah otorisasi (*broken access control* / IDOR) sering tidak menghasilkan error visual—mahasiswa luar kelas atau dosen lain bisa diam-diam mengakses berkas/nilai orang lain bila pemeriksaan policy terlewat. Karena tidak mungkin menguji manual seluruh kombinasi `role × endpoint` di setiap perubahan kode, feature test jalur ditolak (`403 Forbidden`) adalah penjaga utama agar hak akses tidak pernah jebol.

### 4. Apa fungsi `RefreshDatabase`? Kenapa migrasi harus reversible agar ia bekerja?
`RefreshDatabase` menjalankan migrasi skema di awal pengujian lalu membungkus/mereset transaksi basis data untuk setiap test method sehingga antar pengujian tidak saling mengotori data (*isolated & repeatable*). Agar proses reset atau `php artisan migrate:refresh` berjalan mulus tanpa tertahan error sisa constraint/tabel lama (seperti kasus MySQL Error 1553 di `2026_11_01_125818_add_performance_indexes.php`), setiap method `down()` pada migrasi harus mampu membatalkan perubahan `up()` secara bersih (*reversible*).

### 5. Buka `docs/debugging.md`. Ceritakan satu bug: bagaimana Anda mempersempit penyebabnya?
Salah satu bug pada `docs/debugging.md` adalah **Potensi Celah Open Redirect pada `NotificationController@open`** (temuan peer review PR #59):
1. *Gejala:* Parameter `$notification->data['url']` diteruskan langsung ke `redirect($targetUrl)`.
2. *Mempersempit:* Dengan membaca method `open()` di `app/Http/Controllers/NotificationController.php` dan menguji skenario notifikasi berisi URL domain eksternal (`https://evil-phishing-site.test/login`), terlihat bahwa meskipun `Gate::authorize('update', $notification)` memblokir IDOR pengguna lain, belum ada filter host/skema URL tujuan.
3. *Perbaikan & Test Penjaga:* Ditambahkan validasi path relatif internal (diawali `/` tunggal) atau kecocokan host terhadap `config('app.url')` / `$request->getHost()` dengan fallback ke `route('dashboard')`, lalu dikunci oleh 3 test pada `tests/Feature/NotificationQueueTest.php`.

### 6. Kenapa `dd()` tidak boleh masuk PR, sementara `Log::info()` boleh?
`dd()` (*dump and die*) langsung menghentikan eksekusi skrip PHP dan membuang output mentah ke layar HTTP response, yang mematahkan fitur aplikasi serta menggagalkan test suite maupun produksi jika terbawa ke PR. Sebaliknya, `Log::info()` mencatat informasi diagnostik secara senyap ke berkas log server (`storage/logs/laravel.log`) tanpa memutus alur request pengguna atau membocorkan struktur data internal ke antarmuka browser.
