### Nama : Falih Dzakwan

#### NIM : 10241028

---

# MINGGU 13 — Kolaborasi, Version Control, dan Peer Review

Laporan praktikum Minggu 13 ini mencakup empat bagian utama sesuai modul pembelajaran (`06-modul-minggu-13-16.md`):
1. **READ** : Analisis riwayat Git pribadi dan perbandingan kontribusi pada repositori `kampuslms-b-kelompok-03`.
2. **BREAK** : Pengamatan enam skenario keamanan version control dan proteksi kolaborasi tim.
3. **BUILD (Peer Review Kelompok Lain — 10% Individu)** : Laporan Peer Review formal terhadap **Pull Request #81** pada repositori **Kelompok 4** (`Zweilior/kampuslms-kelompok-B4`), lengkap dengan 3 temuan dari kategori berbeda (**Keamanan**, **Performa**, dan **Struktur/Keterbacaan**) beserta komentar siap tempel untuk GitHub PR.
4. **KOLABORASI INTERNAL & TANGGAPAN REVIEW** : Ringkasan rotasi internal (review PR #80 milik Elsya) dan tanggapan resmi atas review dari Fatika Rizki Syahada terhadap Pull Request #57 (`feat(notification): event, listener, dan notifikasi queue untuk tugas baru dan nilai`).

---

## BAGIAN 1: READ — Baca Riwayat Kelompok Anda (30 Menit)

Pemeriksaan riwayat repositori dilakukan menggunakan perintah Git bawaan pada terminal proyek:
```bash
git log --oneline --graph --all
git shortlog -sn --all
git log --pretty='%an' | sort | uniq -c
```

Berikut adalah jawaban reflektif berdasarkan data riwayat Git repositori `kampuslms-b-kelompok-03`:

### 1. Jumlah Commit Atas Nama Sendiri dan Perbandingannya dengan Anggota Lain

Hasil eksekusi `git shortlog -sn --all` pada seluruh branch repositori:

| Peringkat | Kontributor / Akun Git | Jumlah Commit | Persentase Kontribusi | Fokus Area Kontribusi Utama |
|:---:|---|:---:|:---:|---|
| 1 | `Indri6` (Indriani Anwar) | 127 | 35.9% | Seeder database idempotent, konfigurasi Railway, security headers, optimasi query performa |
| 2 | `FalihDzakwan1` (Falih Dzakwan) | 104 | 29.4% | Setup awal Laravel, sistem notifikasi queue & event, batas unggah 5MB, start script, route cache, fitur pratinjau tugas & materi |
| 3 | `elsyanrr` / `Elsyanrr` (Elsya Nur Aulia Handayani) | 76 | 21.5% | Proteksi IDOR rute & controller, layout views, skrip deploy otomatis (`deploy.sh`), konfigurasi supervisor worker |
| 4 | `Fatika` / `Fatika Rizki Syahada` | 47 | 13.2% | Rekapitulasi nilai, migrasi indeks performa, pengujian otorisasi, dokumentasi audit produksi |
| **Total** | **Seluruh Anggota Kelompok 03** | **354** | **100.0%** | **Repositori LMS Kampus terintegrasi penuh** |

**Refleksi Kontribusi Pribadi:**  
Akun saya tercatat dengan nama `FalihDzakwan1` sebanyak **104 commit** (menyumbang 29.4% dari keseluruhan commit repositori), menempatkan saya pada posisi kedua kontributor terbanyak. Kontribusi saya berfokus pada fondasi arsitektur sistem di awal semester, perancangan modul notifikasi asinkron berbasis Event, Listener, dan Queue Worker (Minggu 10), penyusunan test suite otomatis, pengerasan konfigurasi deployment container produksi (Minggu 12), serta penambahan fitur pratinjau berkas terproteksi pada tugas dan materi perkuliahan.

### 2. Konsistensi Keaktifan Mingguan

Berdasarkan penelusuran riwayat tanggal commit (`git log --author="Falih" --date=short`):
* **Minggu 1 s.d. 3 (September 2026):** Aktif melakukan inisialisasi framework Laravel 12, konfigurasi skema basis data awal, pengaturan rute autentikasi, serta CI dasar.
* **Minggu 4 s.d. 7 (September - Oktober 2026):** Mengerjakan pengetatan otorisasi middleware, penutupan celah draft tugas, penyesuaian hak akses role, serta implementasi `UserPolicy`.
* **Minggu 8 (Pertengahan Oktober 2026):** Terdapat jeda aktivitas commit selama sepekan sehubungan dengan pelaksanaan Ujian Tengah Semester (UTS) tertulis.
* **Minggu 9 s.d. 13 (Akhir Oktober - November 2026):** Aktivitas commit berjalan sangat intensif untuk membangun sistem notifikasi antrean latar belakang (PR #57), perbaikan batas unggah berkas 5MB, pembuatan `docker/start.sh`, rate limiting login, hingga penerapan fitur pratinjau dokumen dan pelaksanaan peer review.

### 3. Persentase Kepatuhan Format Conventional Commits

Dari total 104 commit atas nama saya:
* **Mengikuti format Conventional Commits:** 78 commit (~75.0%)
* **Pesan non-standar / informal / merge commits:** 26 commit (~25.0%)

**Analisis Evaluatif:**  
Pada fase awal (Minggu 1 sampai 3), beberapa commit masih menggunakan deskripsi singkat non-baku (contoh: `commit`). Namun, mulai Minggu 4 dan seterusnya, saya secara konsisten menerapkan standar Conventional Commits menggunakan prefix semantik yang jelas seperti `feat:`, `fix:`, `refactor:`, `test:`, dan `docs:`.

### 4. Evaluasi Pesan Commit Terburuk dan Penulisan Ulang

* **Pesan commit terburuk yang ditemukan:**  
  Hash: `bd75dd3`  
  Pesan commit: `commit` (dibuat pada 14 September 2026).
* **Alasan buruk:**  
  Pesan commit ini hanya satu kata, tidak memiliki prefix semantik, dan sama sekali tidak mendeskripsikan perubahan apa yang dibuat serta berkas apa yang dimodifikasi.
* **Penulisan ulang yang seharusnya:**  
  ```text
  docs(minggu-03): tambah catatan praktikum otorisasi dan middleware
  ```

### 5. Evaluasi Commit Terbesar (`git log --stat`)

* **Commit terbesar yang teridentifikasi:**  
  Hash: `2c09c24`  
  Pesan commit: `feat(notification): event, listener, dan notifikasi queue untuk tugas baru dan nilai`  
  Statistik perubahan: 12 berkas, 733 baris penambahan (`+`), 2 baris pengurangan (`-`).  
  Daftar berkas: Event (`AssignmentPublished`, `GradeGiven`), Listener (`SendAssignmentNotification`, `SendGradeNotification`), Notification (`NewAssignmentNotification`, `GradeGivenNotification`), Controller Web/API, dan Feature Test (`NotificationQueueTest`).
* **Analisis pemecahan:**  
  Commit ini mencakup cakupan perubahan yang terlalu luas dalam satu snapshot. Seharusnya dipecah menjadi 3 commit terpisah sesuai prinsip *atomic commit*:
  1. `feat(notification): buat event, listener, dan notification class untuk tugas dan nilai` (khusus berkas di folder `app/Events/`, `app/Listeners/`, dan `app/Notifications/`).
  2. `feat(notification): integrasi pemanggilan event dispatch pada controller web dan api` (khusus berkas di `app/Http/Controllers/`).
  3. `test(notification): tambah automated feature test untuk sistem queue dan payload notifikasi` (khusus berkas `tests/Feature/NotificationQueueTest.php`).

---

## BAGIAN 2: BREAK — Enam Percobaan Version Control & Kolaborasi (40 Menit)

Pengujian skenario kegagalan ini dijalankan pada branch pengujian untuk menguji mekanisme proteksi Git, pipeline CI, dan sistem integritas kolaborasi kelompok:

| No. | Skenario Percobaan | Hasil Pengamatan pada Git / CI / GitHub | Pelajaran Teknis & Tindakan Mitigasi |
|:---:|---|---|---|
| **1** | Membuat berkas `.env` dengan kredensial palsu lalu mencoba melakukan `git push` | Pipeline GitHub Actions CI langsung menolak dan menggagalkan build pada step validasi keamanan (*"Pastikan .env tidak ter-commit"*). | Berkas `.env` wajib dimasukkan ke dalam `.gitignore` sejak inisialisasi awal repositori. Kredensial tidak boleh masuk ke repositori publik karena bot pemindai dapat menyalin rahasia dalam hitungan menit. |
| **2** | Menghapus berkas `.env` dari commit terbaru menggunakan perintah `git rm --cached .env` lalu commit ulang | Berkas fisik `.env` terhapus dari status HEAD branch aktif, namun rekaman berkas dan isinya tetap tersimpan utuh di dalam snapshot riwayat commit sebelumnya (`git log`). Siapa pun dapat melihat isinya dengan memeriksa commit lama. | Sekali berkas rahasia ter-push ke remote publik, menghapusnya di commit terbaru tidak cukup. Rahasia tersebut harus dianggap telah bocor: kata sandi database wajib diganti dan `APP_KEY` harus dibuat ulang (`php artisan key:generate`). |
| **3** | Dua pengembang mengedit baris yang sama pada `routes/web.php` secara bersamaan, lalu dilakukan operasi `merge` | Git menghentikan proses merge secara otomatis dan memunculkan status `CONFLICT (content): Merge conflict in routes/web.php`. Berkas ditandai dengan conflict marker (`<<<<<<<`, `=======`, `>>>>>>>`). | Konflik tidak dapat diselesaikan otomatis oleh sistem. Kedua pengembang harus berdiskusi untuk menentukan baris rute yang benar, menghapus marker manual, lalu menjalankan `git add` dan `git commit` untuk menyelesaikan merge. |
| **4** | Mencoba melakukan push langsung ke branch utama (`git push origin main`) tanpa melalui Pull Request | Perintah push ditolak mentah-mentah oleh server GitHub dengan pesan error: `remote: error: GH006: Protected branch hook declined. Changes must be made through a pull request.` | Fitur *Branch Protection Rules* pada GitHub berhasil mencegah perubahan langsung pada branch produksi tanpa proses peninjauan kode (*peer review*) dan verifikasi kelulusan tes otomatis. |
| **5** | Membuka halaman Pull Request yang status pengujian CI-nya gagal (*failing check*), lalu mencoba menekan tombol merge | Tombol **Merge pull request** pada antarmuka web GitHub berubah menjadi abu-abu dan terkunci (*blocked*), menampilkan peringatan bahwa syarat kelulusan test belum terpenuhi. | Pengaturan branch protection yang mewajibkan status check sukses terbukti efektif menghentikan kode yang rusak (*broken build*) masuk ke branch utama sistem. |
| **6** | Membuat commit dengan memanipulasi identitas pengarang menggunakan perintah `git commit --author="Nama Rekan <rekan@mail.com>"` | Commit berhasil dibuat dan tercatat di `git log` serta `git shortlog` seolah-olah dibuat oleh rekan tersebut, padahal akun lokal yang mengeksekusi berbeda. | Kepenulisan pada riwayat Git lokal dapat dipalsukan jika tidak diproteksi dengan tanda tangan kriptografi (GPG signed commit). Itulah sebabnya penilaian kontribusi riwayat Git tidak dapat hanya berpatokan pada angka numerik `git shortlog`, melainkan harus dikonfirmasi melalui wawancara dan penguasaan kode. |

---

## BAGIAN 3: BUILD — Peer Review Kelompok 4 (Pull Request #81)

Sesuai penugasan **Komponen Review (10% Individu)** pada Modul Minggu 13, saya mengkloning repositori kelompok lain (**Kelompok 4**: `https://github.com/Zweilior/kampuslms-kelompok-B4`), memeriksa alur kode, dan melakukan review objektif terhadap **Pull Request #81**.

### 1. Informasi Pull Request yang Direview

| Keterangan | Informasi Rinci |
|---|---|
| **Repository Tujuan** | `Zweilior/kampuslms-kelompok-B4` (Kelompok 4) |
| **Pull Request** | `#81` (`https://github.com/Zweilior/kampuslms-kelompok-B4/pull/81`) |
| **Author (Pembuat PR)** | `Marchelinosk` (Marchelino Senduk Kaunang) |
| **Reviewer (Pemeriksa)** | `FalihDzakwan1` (Falih Dzakwan / NIM: 10241028 — Kelompok 03) |
| **Branch Sumber → Tujuan** | `Marchelino-1040` → `main` |
| **Hash Commit PR** | `cf2956b` |
| **Fokus Perubahan** | Pembaruan `SubmissionController@store` untuk unggah file sungguhan, mekanisme kumpul ulang, validasi tenggat (`allow_late`), dan penghapusan file lama |

**Judul PR yang Diperiksa:**  
`feat(submission): perbarui method store untuk unggah file sungguhan, kumpul ulang, dan pengamanan`

---

### 2. Berkas yang Diperiksa

Pada Pull Request #81, berkas yang secara langsung diubah (*Git diff*) adalah **1 berkas**. Namun, dalam proses *code review* profesional, penelaahan kode tidak dapat berdiri sendiri tanpa memeriksa berkas dependensi yang dipanggil langsung oleh method tersebut. Oleh karena itu, berkas yang diperiksa dikategorikan menjadi:

#### A. Berkas Utama yang Diubah pada PR #81 (Files Changed: 1 Berkas)

| Berkas yang Diubah | Baris Terdampak | Cakupan Perubahan (*Git Diff*) |
|---|:---:|---|
| `app/Http/Controllers/SubmissionController.php` | 212 – 286 (`+72`, `-21`) | Mengganti input string `file_path` menjadi unggahan berkas fisik melalui `StoreSubmissionRequest`, validasi kumpul ulang setelah dinilai, cek `allow_late`, penyimpanan ke disk `local`, hapus berkas lama, dan pengiriman notifikasi `PengumpulanBaru`. |

#### B. Berkas Konteks Terkait yang Turut Ditinjau (*Context & Dependency Files*)

Berkas-berkas berikut tidak mengalami perubahan langsung di PR #81 (merupakan hasil commit/PR sebelumnya seperti PR #80), namun **wajib ditinjau bersamaan** karena dipanggil langsung di dalam method `store()` PR #81 untuk memverifikasi keabsahan validasi input dan sistem otorisasi:

| Berkas Dependensi / Konteks | Peran & Alasan Ikut Ditinjau |
|---|---|
| `app/Http/Requests/StoreSubmissionRequest.php` | Dipakai sebagai *Form Request type-hint* pada parameter `store()`. Ditinjau untuk memastikan aturan validasi berkas fisik (`mimes:pdf,doc,docx`, ukuran max `5120` KB, dan batas sanitasi `note`). |
| `app/Policies/SubmissionPolicy.php` | Dipanggil melalui `Gate::authorize('create', [Submission::class, $assignment])`. Ditinjau untuk menelusuri bagaimana Laravel Policy memverifikasi hak akses mahasiswa terhadap tugas & mata kuliah tersebut (krusial untuk mengidentifikasi temuan celah keamanan relasi URL). |

---

### 3. Hasil Pemeriksaan Kode (3 Kategori Wajib)

```
+-------------------------------------------------------------------------+
|                  RINGKASAN DIMENSI TEMUAN REVIEW                        |
|               (Kelompok 4 — Pull Request #81 — cf2956b)                 |
+-------------------------------------------------------------------------+
|  A. Keamanan  : Tidak ada validasi relasi $assignment->course_id vs     |
|                 $course->id (kebocoran notifikasi ke dosen MK lain)     |
|  B. Performa  : Query SELECT duplikat (existingSubmission +             |
|                 updateOrCreate) serta potensi lazy loading              |
|  C. Struktur  : Risiko berkas hilang / data korup pada alur rollback          |
|                 try-catch & variabel $isLate tidak tersimpan ke database   |
+-------------------------------------------------------------------------+
```

#### A. Keamanan, Tidak Ada Validasi Konsistensi Relasi `$assignment` terhadap `$course` pada Parameter URL

* **Lokasi:** `app/Http/Controllers/SubmissionController.php:212-217` dan `266-269` (`method store()`)
* **Kode yang Diperiksa:**
  ```php
  public function store(
      StoreSubmissionRequest $request,
      Course $course,
      Assignment $assignment
  ) {
      Gate::authorize('create', [Submission::class, $assignment]);
      ...
      // 6. Kirim notifikasi PengumpulanBaru ke Dosen
      $lecturer = $course->lecturer;
      if ($lecturer) {
          $lecturer->notify(new PengumpulanBaru($submission));
      }
  ```
* **Temuan & Risiko:**
  Method `store()` menerima dua parameter route model binding (`Course $course` dan `Assignment $assignment`), namun hanya memanggil `Gate::authorize('create', [Submission::class, $assignment])` yang mengecek enrollment pada `$assignment->course`, tanpa memverifikasi bahwa `$assignment->course_id === $course->id`. Apabila mahasiswa memodifikasi segmen `{course}` di URL ke ID mata kuliah lain sementara `{assignment}` mengarah ke tugas mata kuliah yang ia ikuti, otorisasi akan tetap lolos. Akibatnya, pada baris `266-269`, sistem akan membaca `$course->lecturer` dari mata kuliah yang salah dan **membocorkan notifikasi serta tautan pengumpulan tugas ke dosen mata kuliah lain** yang tidak berhak, sekaligus mengalihkan redirect ke URL mata kuliah yang tidak sesuai.
* **Saran Perbaikan:**
  Tambahkan pengecekan relasi induk-anak di awal method `store()` (atau aktifkan `->scopeBindings()` pada definisi nested route di `routes/web.php`):
  ```php
  abort_unless((int) $assignment->course_id === (int) $course->id, 404);
  ```
  Serta ambil objek dosen dari `$assignment->course->lecturer` agar konsisten dengan kepemilikan tugas.

---

#### B. Performa, Query `SELECT` Redundan dan Potensi Lazy Loading pada Proses Unggah

* **Lokasi:** `app/Http/Controllers/SubmissionController.php:220-258` dan `266` (`method store()`)
* **Kode yang Diperiksa:**
  ```php
  $existingSubmission = $assignment->submissions()
      ->where('user_id', $request->user()->id)
      ->first();

  if ($existingSubmission && $existingSubmission->grade()->exists()) { ... }
  ...
  $submission = Submission::updateOrCreate(
      ['assignment_id' => $assignment->id, 'user_id' => $request->user()->id],
      [ ... ]
  );
  ...
  $lecturer = $course->lecturer;
  ```
* **Temuan & Risiko:**
  1. Baris `220-222` sudah melakukan query `SELECT` untuk mengambil `$existingSubmission`, namun tidak memuat status keberadaan nilai (`withExists('grade')`), sehingga baris `225` memicu query tambahan `$existingSubmission->grade()->exists()`.
  2. Baris `246-258` memanggil `Submission::updateOrCreate(...)`, yang secara internal mengeksekusi **query `SELECT` ulang** terhadap kombinasi `(assignment_id, user_id)` padahal model `$existingSubmission` sudah diambil pada baris `220`.
  3. Pemanggilan `$course->lecturer` (baris `266`) dan `$submission->assignment->course` di dalam Policy berpotensi memicu *lazy loading* (`LazyLoadingViolationException` di lingkungan non-produksi saat `Model::preventLazyLoading()` aktif) karena relasi `course.lecturer` dan `assignment.course` belum di-eager load.
* **Saran Perbaikan:**
  Gunakan `withExists('grade')` saat mengambil `$existingSubmission`, lalu lakukan percabangan `update()` atau `create()` langsung tanpa `updateOrCreate()` agar tidak terjadi query `SELECT` ganda:
  ```php
  $assignment->loadMissing('course.lecturer');
  $existingSubmission = $assignment->submissions()
      ->where('user_id', $request->user()->id)
      ->withExists('grade')
      ->first();

  if ($existingSubmission?->grade_exists) {
      throw ValidationException::withMessages([
          'file' => 'Tugas sudah dinilai dan tidak dapat dikumpulkan ulang.',
      ]);
  }
  ```

---

#### C. Struktur dan Konsistensi Data, Risiko Berkas Hilang Akibat Alur Rollback `try-catch` yang Tidak Tepat dan Variabel `$isLate` Tidak Tersimpan

* **Lokasi:** `app/Http/Controllers/SubmissionController.php:232-285` (`method store()`)
* **Kode yang Diperiksa:**
  ```php
  $isLate = now()->gt($assignment->due_at);
  ...
  try {
      $submission = Submission::updateOrCreate([...], [
          'file_path' => $newFilePath,
          ...
          'submitted_at' => now(),
      ]);

      if ($oldFilePath && Storage::disk('local')->exists($oldFilePath)) {
          Storage::disk('local')->delete($oldFilePath);
      }

      $lecturer = $course->lecturer;
      if ($lecturer) {
          $lecturer->notify(new PengumpulanBaru($submission));
      }
      ...
  } catch (\Throwable $e) {
      if (Storage::disk('local')->exists($newFilePath)) {
          Storage::disk('local')->delete($newFilePath);
      }
      throw $e;
  }
  ```
* **Temuan & Risiko:**
  1. **Risiko Data Korup di `try-catch` (`baris 244-285`):** `updateOrCreate()` langsung melakukan commit ke database di baris `246`, lalu baris `261-263` menghapus berkas lama (`$oldFilePath`), baru kemudian baris `268` mengirim notifikasi. Jika pengiriman notifikasi (baris `268`) gagal dan melempar exception (misal koneksi queue/mail bermasalah), eksekusi masuk ke blok `catch` (baris `278`) dan **menghapus `$newFilePath` yang baru saja diunggah**, padahal baris di database sudah terlanjur menunjuk ke `$newFilePath` dan `$oldFilePath` sudah terhapus! Akibatnya record submission tetap ada di DB tetapi berkas fisiknya hilang permanen (404 saat diunduh dosen).
  2. **Variabel Mati `$isLate` (`baris 232`):** Variabel `$isLate` sudah dihitung dengan benar, tetapi tidak ikut disimpan ke dalam array atribut `updateOrCreate()` (baris `251-257`).
* **Saran Perbaikan:**
  Bungkus hanya operasi penyimpanan database dengan `DB::transaction()`, pindahkan penghapusan `$oldFilePath` dan pengiriman notifikasi ke **luar** blok `try-catch` rollback file (tepat setelah transaksi DB berhasil), serta simpan nilai `'is_late' => $isLate` jika kolom tersedia pada tabel `submissions`:
  ```php
  $submission = DB::transaction(function () use ($assignment, $request, $newFilePath, $file, $existingSubmission, $isLate) {
      $data = [
          'file_path'     => $newFilePath,
          'original_name' => $file->getClientOriginalName(),
          'file_size'     => $file->getSize(),
          'note'          => $request->validated()['note'] ?? null,
          'submitted_at'  => now(),
      ];
      return $existingSubmission
          ? tap($existingSubmission)->update($data)
          : $assignment->submissions()->create([...$data, 'user_id' => $request->user()->id]);
  });

  // Hapus file lama & kirim notifikasi hanya setelah DB & file baru aman
  if ($oldFilePath && $oldFilePath !== $newFilePath) {
      Storage::disk('local')->delete($oldFilePath);
  }
  $assignment->course->lecturer?->notify(new PengumpulanBaru($submission));
  ```

---

## BAGIAN 4: KOLABORASI INTERNAL dan TANGGAPAN REVIEW (KELOMPOK 03)

Selain peer review ke Kelompok 4, kelompok kami juga menjalankan rotasi review internal dan menanggapi temuan review yang masuk ke PR kami sendiri:

### A. Ringkasan Review Internal (PR #80 — Elsya Nur Aulia Handayani)
Pada rotasi internal Kelompok 03, saya memeriksa PR #80 (`deploy.sh` & `supervisord.conf`) dengan 3 temuan yang telah diterima dan diperbaiki:
1. **Keamanan (`deploy.sh`):** Ketiadaan `trap 'php artisan up' EXIT` saat `set -e` aktif berisiko membuat server terkunci pada mode maintenance (HTTP 503) jika deploy gagal di tengah jalan. *(Diperbaiki pada commit `b72de93` / `deploy.sh`)*.
2. **Performa (`deploy.sh`):** Belum ada `php artisan event:cache` untuk mengompilasi listener notifikasi antrean Laravel 12. *(Diperbaiki di `deploy.sh`)*.
3. **Struktur (`deploy.sh`):** Pemanggilan `npm` belum memiliki fallback check `command -v npm`. *(Diperbaiki di `deploy.sh`)*.

### B. Tanggapan Resmi Atas Review Masuk dari Fatika Rizki Syahada (PR #57)
Pada PR #57 milik saya (`feat(notification): event, listener, dan notifikasi queue untuk tugas baru dan nilai`), Fatika Rizki Syahada memberikan 3 temuan yang juga telah saya tanggapi secara resmi di [docs/minggu-13-Fatika Rizki Syahada.md](../minggu-13-Fatika%20Rizki%20Syahada.md#L262-L295):

| No. | Dimensi Temuan | Ringkasan Temuan Fatika | Keputusan & Tanggapan Falih Dzakwan | Bukti Tindak Lanjut |
|:---:|---|---|---|:---|
| **1** | **Keamanan** | Parameter `score` di `Api/GradeController@upsert` belum memvalidasi batas maksimum (`max:`). | **DITERIMA & DIPERBAIKI:** Ditambahkan validasi dinamis `'max:' . $maxScore` sesuai bobot tugas (`$submission->assignment->max_score ?? 100`). | Diperbaiki di `app/Http/Controllers/Api/GradeController.php:32-37`. |
| **2** | **Performa** | `SendAssignmentNotification` memakai `->get()` tanpa `->chunk()`. | **DITOLAK DENGAN ALASAN SAH:** Kapasitas kelas LMS saat ini 30–40 mahasiswa sehingga `get()` sangat ringan (<5 ms) dan dispatch sudah asinkron di background queue; `chunk()` pada dataset kecil justru menambah overhead query. | Dipertahankan demi efisiensi query tunggal. |
| **3** | **Struktur** | Lazy loading relasi berantai pada `GradeGivenNotification::toDatabase()`. | **DITOLAK DENGAN ALASAN SAH:** Relasi sudah dimuat pada listener sebelum job dikirim dan Laravel Queue Serializer memulihkan model secara aman; menghindari boilerplate konstruktor berlebih (KISS/YAGNI). | Dipertahankan demi kesederhanaan class notifikasi. |

---

## BAGIAN 5: KESIMPULAN

Melalui rangkaian praktikum Minggu 13 (READ, BREAK, dan BUILD), dapat disimpulkan bahwa kualitas pengembangan perangkat lunak kolaboratif sangat bergantung pada disiplin version control dan keobjektifan *peer review*:

1. **Disiplin Commit (READ):** Pemeriksaan riwayat Git menunjukkan kontribusi 104 commit dengan tren kepatuhan *Conventional Commits* yang meningkat signifikan sejak Minggu 4, sekaligus memberi kesadaran pentingnya memecah *large commit* menjadi *atomic commit* yang terfokus.
2. **Proteksi Repositori & CI (BREAK):** Enam percobaan kegagalan membuktikan bahwa `.gitignore`, GitHub Actions CI, *Branch Protection Rules*, dan pemeriksaan status check wajib berjalan bersama untuk mencegah bocornya `.env` maupun masuknya kode rusak ke branch `main`.
3. **Peer Review Objektif Lintas Kelompok & Internal (BUILD):** Peninjauan PR #81 milik Kelompok 4 (`Zweilior/kampuslms-kelompok-B4`) berhasil mengidentifikasi temuan nyata di tiga dimensi wajib (**Keamanan**, **Performa**, dan **Struktur & Konsistensi Data**), lengkap dengan konteks berkas dependensi terkait. Selain itu, seluruh temuan pada rotasi internal Kelompok 03 telah ditindaklanjuti baik melalui perbaikan kode (`fix:`) maupun argumentasi teknis yang terukur.
