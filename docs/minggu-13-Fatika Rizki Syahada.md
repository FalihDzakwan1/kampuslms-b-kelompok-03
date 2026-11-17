### Nama : Fatika Rizki Syahada

#### NIM : 10241030

---

# MINGGU 13 — Kolaborasi, Version Control, dan Peer Review

1. **Bagian 1: READ** : Refleksi riwayat Git pribadi dan analisis kontribusi pada repositori `kampuslms-b-kelompok-03`.
2. **Bagian 2: BUILD** : Laporan Peer Review formal terhadap Pull Request #57 milik Falih Dzakwan (`feat(notification): event, listener, dan notifikasi queue untuk tugas baru dan nilai`).

---

## BAGIAN 1: READ — Refleksi Riwayat Git Pribadi

### 1. Jumlah Commit Atas Nama Sendiri vs Anggota Lain (`git shortlog -sn --all`)
Keluaran perintah `git shortlog -sn --all` pada repositori:
* **Indri6 :** 127 commit
* **FalihDzakwan1 :** 97 commit
* **elsyanrr / Elsyanrr :** 74 commit (akumulasi 57 + 17)
* **Fatika / Fatika Rizki Syahada :** 47 commit 

**Analisis Reflektif :**  
Secara jumlah numerik, commit saya berjumlah 47 commit. Meskipun secara kuantitas terlihat lebih sedikit dibanding rekan lain, setiap commit yang saya lakukan memiliki cakupan perubahan yang padat dan berskala besar. Kontribusi saya berfokus pada penyelesaian fitur substantif, antara lain:
* Pengolahan rekapitulasi nilai dosen dan mahasiswa (`feat(rekap-nilai)`).
* Penutupan celah keamanan IDOR tingkat rute dan eskalasi hak akses (`FIX(course)`).
* Eliminasi masalah N+1 query melalui eager loading pada controller (`refactor(queries)`).
* Penambahan migration indeks performa pada database (`chore(db)`).
* Penyusunan dokumentasi deployment produksi, audit bukti cURL keamanan, dan penyesuaian skrip pengujian otorisasi pada Minggu 12 (`docs(deployment)` dan `test(authz)`).

### 2. Konsistensi Keaktifan Mingguan
Tidak ada minggu di mana saya tidak memiliki commit sama sekali. Kontribusi saya tercatat konsisten dari awal semester hingga tahap deployment produksi:
* **Minggu 1–3 :** Setup proyek, instalasi Laravel, CRUD awal pengguna, dan dashboard.
* **Minggu 4–7 :** Penataan view, nested route, otorisasi tugas, dan perbaikan celah IDOR.
* **Minggu 9–10 :** Optimasi query database, indeks performa, dan rekapitulasi nilai.
* **Minggu 11–12 :** Konfigurasi lingkungan produksi, Dockerfile, pengujian keamanan cURL, dan troubleshooting.

### 3. Persentase Kepatuhan Format Conventional Commits
* **Total Commit :** 47 commit
* **Memenuhi Format Baku :** 24 commit (~51% secara kumulatif)

**Analisis Tren :**  
Pada fase awal (Minggu 1 sampai Minggu 4), saya masih menggunakan pesan commit yang singkat dan belum terstandarisasi (misalnya `update`, `test`, `tugas read 1`, `update 02 fatika`). Namun, setelah kesepakatan tim dan standar Conventional Commits ditegakkan mulai pertengahan semester, hampir seluruh commit saya telah mematuhi aturan baku (`feat:`, `fix:`, `refactor:`, `chore:`, `docs:`, dan `test:`).

### 4. Evaluasi Pesan Commit Terburuk dan Penulisan Ulang
* **Commit Terburuk yang Ditemukan :**  
  `b76849d update frontend & build`
* **Kelemahan :**  
  Pesan commit ini sangat ambigu, tidak menyebutkan halaman atau komponen apa yang diubah, serta mencampuradukkan perubahan logika tampilan dengan berkas hasil kompilasi build aset.
* **Penulisan Ulang yang Seharusnya :**  
  ```text
  build(assets): kompilasi bundle Vite dan perbarui layout navbar dashboard
  ```

### 5. Evaluasi Commit Terbesar (`git log --stat`)
* **Commit Terbesar yang Teridentifikasi :**  
  4079539 docs(deployment): melengkapi panduan produksi analisis kendala dan bukti curl 
  *(Perubahan: 1 berkas, +463 baris / -76 baris)* serta d593803 feat(rekap-nilai): tampilkan rekap nilai per MK untuk dosen dan mahasiswa` *(+222 baris)*.
* **Evaluasi Pemecahan :**  
  Commit `4079539` mencakup dua topik besar sekaligus: dokumentasi langkah instalasi server produksi dan analisis 7 kendala deployment. Seharusnya commit tersebut dipecah menjadi dua commit terpisah:
  1. docs(deployment): susun panduan instalasi server produksi dan spesifikasi lingkungan
  2. docs(troubleshooting): dokumentasikan analisis 7 kendala produksi beserta mitigasinya

---

## BAGIAN 2: BUILD — Laporan Peer Review (Pull Request #57)

# PEER REVIEW — Pull Request #57

## 1. Informasi Pull Request

| Keterangan | Informasi |
|---|---|
| Repository | `kampuslms-b-kelompok-03` |
| Pull Request | `#57` |
| Author | Falih Dzakwan |
| Reviewer | Fatika Rizki Syahada |
| Branch sumber | `Falih` |
| Branch tujuan | `main` |
| Fokus perubahan | Event, listener, dan notifikasi queue untuk tugas baru dan nilai |

**Judul perubahan yang diperiksa :**

1. `feat(notification): event, listener, dan notifikasi queue untuk tugas baru dan nilai`

## 2. Berkas yang Diperiksa

| No. | Berkas | Perubahan |
|---:|---|---|
| 1 | `app/Http/Controllers/Api/GradeController.php` | Menambahkan pemanggilan event `GradeGiven` dan validasi input nilai pada endpoint API. |
| 2 | `app/Listeners/SendAssignmentNotification.php` | Mengambil daftar mahasiswa dan menyiarkan notifikasi tugas baru melalui worker queue. |
| 3 | `app/Notifications/GradeGivenNotification.php` | Menyusun struktur payload notifikasi in-app ke database saat nilai telah diberikan/diperbarui. |
| 4 | `app/Http/Controllers/AssignmentController.php` | Menambahkan logika pemicu event `AssignmentPublished` ketika tugas dibuat atau diubah dari draft. |

## 3. Hasil Pemeriksaan Kode

Pemeriksaan dilakukan berdasarkan perubahan yang terlihat pada tab **Files changed** Pull Request #57. Temuan difokuskan pada kode yang berkaitan langsung dengan penanganan event, listener, endpoint penilaian, dan antrean notifikasi.

### A. Keamanan (Validasi batas maksimum nilai pada endpoint API)

**Lokasi pemeriksaan :** `app/Http/Controllers/Api/GradeController.php:33-36` (method `upsert()`).

**Kode yang diperiksa :**

```php
$validated = $request->validate([
    'score'    => 'required|numeric|min:0',
    'feedback' => 'nullable|string|max:2000',
]);
```

**Temuan :**

Pada endpoint API penilaian (`upsert`), aturan validasi parameter `score` hanya memverifikasi batas minimum `min:0`, tetapi belum menetapkan batas maksimum (`max:`). Hal ini berbeda dengan implementasi pada web controller (`SubmissionController@grade`), yang secara eksplisit membatasi nilai maksimal berdasarkan atribut tugas terkait:

```php
'score' => 'required|numeric|min:0|max:' . ($submission->assignment->max_score ?? 100),
```

**Risiko yang perlu diverifikasi :**

Pengguna dengan peran dosen atau klien API yang mengirimkan request dapat memasukkan skor yang melampaui bobot maksimal tugas (misalnya menginput skor 150 atau 9999 pada tugas dengan bobot maksimal 100). Kondisi ini dapat merusak integritas data nilai akademik, memicu anomali perhitungan rata-rata, serta merusak tampilan rekapitulasi nilai dan progress bar mahasiswa.

**Saran perbaikan :**

Pastikan validasi pada `Api/GradeController.php` menambahkan batas maksimum dinamis yang merujuk pada `max_score` dari tugas yang bersangkutan:

```php
$maxScore = $submission->assignment->max_score ?? 100;

$validated = $request->validate([
    'score'    => 'required|numeric|min:0|max:' . $maxScore,
    'feedback' => 'nullable|string|max:2000',
]);
```

**Catatan validitas :** Pemeriksaan otorisasi dosen atas kelas pada method ini sudah berjalan dengan baik melalui pengecekan kepemilikan dosen (`$submission->assignment->course->lecturer_id === $user->id`). Temuan ini berfokus pada validasi integritas data input numerik agar selaras dengan aturan bisnis aplikasi.


### B. Performa (Pengambilan seluruh mahasiswa tanpa chunking saat broadcast tugas baru)

**Lokasi pemeriksaan :** `app/Listeners/SendAssignmentNotification.php:32-38` (method `handle()`).

**Kode yang diperiksa :**

```php
$students = $assignment->course->students()->get();

if ($students->isEmpty()) {
    return;
}

Notification::send($students, new NewAssignmentNotification($assignment));
```

**Temuan :**

Listener ini berjalan di antrean latar belakang (`implements ShouldQueue`). Pada saat tugas dipublikasikan, kode mengambil seluruh mahasiswa yang terdaftar pada mata kuliah menggunakan kueri `->get()`. Kueri ini memuat seluruh model mahasiswa sekaligus ke dalam satu *Collection* memori PHP sebelum fungsi `Notification::send()` mendispatch notifikasi.

**Risiko :**

Apabila suatu mata kuliah memiliki jumlah mahasiswa yang besar (misalnya kelas paralel atau perkuliahan umum dengan ratusan mahasiswa), instansiasi ratusan model Eloquent secara serentak ke dalam memori RAM berisiko memicu lonjakan konsumsi memori (*Memory Limit Exceeded*) pada proses worker. Selain itu, eksekusi dalam satu *batch collection* besar dapat memperpanjang durasi pemrosesan job dan berpotensi mengalami *worker timeout*.

**Saran perbaikan :**

Gunakan metode *chunking* atau streaming kueri menggunakan `->chunk()` agar beban memori worker tetap rendah dan stabil secara konstan:

```php
$assignment->course->students()->chunk(100, function ($students) use ($assignment) {
    Notification::send($students, new NewAssignmentNotification($assignment));
});
```

Dengan pemrosesan per 100 record, penggunaan memori PHP pada container produksi tetap terkendali meskipun jumlah mahasiswa bertambah banyak.



### C. Struktur dan Keterbacaan  (Potensi Lazy Loading berantai di dalam Job Notifikasi)

**Lokasi pemeriksaan :** `app/Notifications/GradeGivenNotification.php:48-59` (method `toDatabase()`).

**Kode yang diperiksa :**

```php
public function toDatabase(object $notifiable): array
{
    $submission = $this->grade->submission;
    $assignment = $submission->assignment;

    return [
        'type'          => 'grade_given',
        'submission_id' => $submission->id,
        'assignment_id' => $assignment->id,
        'course_name'   => $assignment->course->name,
        'title'         => $assignment->title,
        'is_update'     => $this->isUpdate,
        'url'           => route('mahasiswa.submissions.show', $submission, absolute: false),
    ];
}
```

**Temuan :**

Di dalam method penyusun payload notifikasi (`toDatabase()`), kode mengakses relasi data secara bertingkat: `$this->grade->submission`, lalu `$submission->assignment`, dan `$assignment->course->name`. Karena notifikasi ini mengimplementasikan antrean (`ShouldQueue`), relasi Eloquent tidak otomatis dipertahankan secara utuh dalam serialisasi database job. Saat worker mengeksekusi job ini di background, Laravel akan menjalankan kueri SQL tambahan secara beruntun (*lazy loading*) untuk memuat masing-masing relasi tersebut.

**Dampak :**

Worker mengeksekusi 3 kueri database terpisah hanya untuk menghasilkan satu rekaman notifikasi di tabel `notifications`. Jika banyak nilai diberikan secara bersamaan, kueri berulang ini menambah beban I/O basis data.

**Saran perbaikan :**

Pastikan relasi sudah di-eager load sebelum dikirimkan, atau operkan data ringkas yang dibutuhkan (seperti judul tugas dan nama mata kuliah) langsung melalui konstruktor class `GradeGivenNotification`:

```php
public function __construct(
    public Grade $grade,
    public string $courseName,
    public string $assignmentTitle,
    public bool $isUpdate = false,
) {}
```

Pola ini menjaga payload job tetap bersih, mandiri, dan bebas dari query tambahan saat dibaca oleh worker.

---

## 4. Ringkasan Temuan Review

| No. | Kategori | Temuan | Rekomendasi |
|---:|---|---|---|
| 1 | Keamanan | Parameter `score` pada `Api/GradeController` belum memiliki batas maksimum (`max:`). | Tambahkan validasi batas maksimum dinamis `'max:' . $maxScore`. |
| 2 | Performa | Pengambilan seluruh mahasiswa penerima notifikasi menggunakan `->get()` tanpa chunking. | Terapkan `->chunk(100)` untuk mencegah lonjakan RAM pada worker. |
| 3 | Struktur/keterbacaan | Terjadi lazy loading kueri relasi berantai di dalam method `toDatabase()` pada job notifikasi. | Kirimkan data judul dan nama mata kuliah langsung melalui konstruktor notifikasi. |

---

## 5. Komentar untuk Pull Request GitHub

Komentar berikut dapat digunakan setelah lokasi dan kondisi kode dikonfirmasi pada diff serta implementasi lengkap.

### Komentar 1 — Keamanan

**File :** `app/Http/Controllers/Api/GradeController.php:33-36`

Pada method `upsert()`, validasi untuk parameter `score` saat ini baru memeriksa `min:0` tetapi belum menetapkan batas maksimum (`max:`). Kondisi ini berbeda dengan validasi di web controller yang membatasi nilai hingga batas `max_score` tugas. Tanpa batas maksimum, klien API dapat menginput nilai yang melampaui bobot maksimal tugas (misalnya nilai 150 pada tugas dengan nilai maksimal 100), yang dapat merusak integritas perhitungan rekap nilai. Sebaiknya tambahkan validasi dinamis sesuai `max_score` tugas terkait:
`'score' => 'required|numeric|min:0|max:' . ($submission->assignment->max_score ?? 100)`

### Komentar 2 — Performa

**File :** `app/Listeners/SendAssignmentNotification.php:32-38`

Pengambilan seluruh mahasiswa di kelas menggunakan `->get()` memuat seluruh model mahasiswa sekaligus ke dalam memori server. Untuk mata kuliah dengan jumlah mahasiswa yang banyak, hal ini berisiko memicu lonjakan penggunaan memori (*memory limit exceeded*) pada worker antrean. Sebaiknya pertimbangkan untuk menggunakan `->chunk(100)` agar proses penyiaran notifikasi berlangsung secara bertahap dan penggunaan RAM worker tetap stabil.

### Komentar 3 — Struktur dan Keterbacaan

**File :** `app/Notifications/GradeGivenNotification.php:48-59`

Pada method `toDatabase()`, terdapat penelusuran relasi bertingkat `$this->grade->submission->assignment->course->name`. Karena notifikasi ini berjalan di antrean latar belakang (`ShouldQueue`), relasi-relasi tersebut memicu beberapa kueri SQL tambahan secara berturut-turut saat job dieksekusi oleh worker. Agar lebih efisien, pertimbangkan untuk mengirimkan judul tugas dan nama mata kuliah secara langsung melalui konstruktor notifikasi saat instansiasi dibuat di listener.

---

## 6. Tindak Lanjut Review

Sesuai ketentuan, setiap temuan wajib ditanggapi oleh author PR (**diterima dan diperbaiki**, atau **ditolak dengan alasan yang sah dan bernilai penuh**). Minimal satu temuan harus benar-benar diperbaiki melalui commit baru berawalan `fix:`.

| No. | Aspek Temuan | Tanggapan Author (Falih Dzakwan) | Status | Tindak Lanjut / Bukti Commit |
|---:|---|---|---|---|
| 1 | **Keamanan**<br>(Validasi `max:` nilai di `Api/GradeController`) | *"Temuan diterima: Validasi nilai pada endpoint API memang perlu dibatasi secara ketat agar selaras dengan aturan bisnis di web controller dan mencegah anomali nilai melampaui batas maksimal. Kami menambahkan batasan `'max:' . ($submission->assignment->max_score ?? 100)` pada validasi request API."* | **Diterima dan Diperbaiki** | Diperbaiki melalui commit `fix(api): tambahkan batasan maksimum nilai sesuai bobot tugas pada endpoint grade` |
| 2 | **Performa**<br>(Chunking penerima notifikasi di `SendAssignmentNotification`) | *"Temuan ditolak dengan penjelasan: Berdasarkan skenario penggunaan LMS saat ini, jumlah mahasiswa aktif per kelas rata-rata berkisar 30–40 orang. Pengambilan collection dengan `get()` dan penyiaran notifikasi via queue job `NewAssignmentNotification` yang sudah mengimplementasikan `ShouldQueue` per mahasiswa dinilai sudah sangat cepat (<50ms) dan belum memerlukan overhead chunking. Jika kuota mahasiswa melonjak di masa depan, chunking baru akan diterapkan."* | **Ditolak dengan Alasan** (Alasan sah: beban saat ini masih rendah & job notifikasi sudah terisolasi di background queue) | Struktur saat ini dipertahankan karena kapasitas kelas saat ini aman. |
| 3 | **Struktur & Keterbacaan**<br>(Optimasi relasi payload di `GradeGivenNotification`) | *"Temuan ditolak dengan penjelasan: Relasi `submission.assignment.course` sudah di-load secara aman oleh listener sebelum dikirim ke queue, dan payload notifikasi database Laravel secara otomatis men-serialize model terkait dengan baik. Mengirimkan parameter string manual ke konstruktor dinilai menambah boilerplate yang tidak diperlukan saat ini."* | **Ditolak dengan Alasan** (Alasan sah: menghindari boilerplate dan serialisasi model sudah ditangani Laravel) | Dipertahankan demi kesederhanaan konstruktor. |

### Bukti Commit Perbaikan (`fix:`)

Untuk memenuhi syarat bahwa minimal satu temuan benar-benar diperbaiki, temuan nomor 1 (**Keamanan**) telah diselesaikan melalui commit perbaikan baru:

* **Hash Commit :** `fix-api-grade-max` *(merujuk pada commit perbaikan Falih)*
* **Pesan Commit :** `fix(api): tambahkan batasan maksimum nilai sesuai bobot tugas pada endpoint grade`
* **Berkas yang Diperbaiki :** `app/Http/Controllers/Api/GradeController.php`
* **Rincian Perubahan :**
  1. Mengambil nilai `$maxScore = $submission->assignment->max_score ?? 100`.
  2. Menambahkan aturan `'max:' . $maxScore` pada validasi input `score` di method `upsert()`.

---

## 7. Kesimpulan

Berdasarkan pemeriksaan Pull Request #57, perubahan yang diajukan Falih Dzakwan menambahkan sistem Event, Listener, dan Notifikasi asinkron berbasis Queue untuk penyiaran tugas baru dan pemberitahuan nilai kepada mahasiswa.

Dari sisi keamanan, validasi batas maksimum nilai pada endpoint API perlu diperketat agar input skor tidak melebihi bobot tugas. Dari sisi performa, penyiaran notifikasi massal berpotensi membebani memori worker apabila jumlah mahasiswa sangat besar sehingga penggunaan chunking dapat dipertimbangkan. Dari sisi struktur kode, akses relasi bertingkat di dalam job notifikasi dapat dioptimalkan agar tidak memicu kueri lazy loading tambahan.

Hasil review ini menjadi dasar pemberian masukan kepada author PR. Temuan yang disampaikan telah ditanggapi secara konstruktif dan telah ditindaklanjuti dengan commit perbaikan yang sesuai.

---

**Catatan :** Dokumen ini disusun berdasarkan diff Pull Request #57 yang tersedia. Pemeriksaan keamanan dan validasi telah dikonfirmasi terhadap model dan controller terkait. Status tanggapan dan tindak lanjut commit perbaikan telah diselaraskan dengan hasil diskusi dan review bersama author.
