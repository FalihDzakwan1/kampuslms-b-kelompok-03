### Nama : Fatika Rizki Syahada
#### NIM : 10241030

---

# MINGGU 13 — Kolaborasi, Version Control, dan Peer Review

1. **Bagian 1: READ** : Refleksi riwayat Git pribadi dan analisis kontribusi pada repositori `kampuslms-b-kelompok-03`.
2. **Bagian 2: BUILD** : Laporan Peer Review formal terhadap Pull Request kelompok lain (Pull Request #149 milik Laudya Aprilia Khoirum / Kelompok B4).
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

* **Pesan commit terburuk yang ditemukan:**  
  Hash: `b76849d`  
  Pesan commit: `update frontend & build`  
  Tanggal: 13 September 2026  
  Berkas yang diubah: `resources/views/courses/index.blade.php`, `resources/css/app.css`, dan `app/Http/Controllers/UserController.php` (+1.418 baris, -270 baris).

* **Alasan buruk / kelemahan:**  
  1. **Tidak mematuhi standar Conventional Commits:** Tidak memiliki tag tipe (`feat`, `style`, `refactor`) maupun scope.
  2. **Ambigu dan terlalu luas:** Istilah *"update frontend & build"* tidak menjelaskan bagian mana yang diperbarui, mengapa diubah, dan apa dampaknya bagi pengguna.
  3. **Mencampurkan multi-tanggung jawab (*mixed concerns*):** Commit ini menggabungkan perubahan tampilan halaman daftar mata kuliah (`courses/index.blade.php`), penambahan ribuan baris styling CSS (`app.css`), serta penyesuaian controller pengguna (`UserController.php`) dalam satu commit tanpa penjelasan yang terpisah.

* **Penulisan ulang yang seharusnya:**  
  Jika tetap dalam satu commit:
  ```text
  style(courses): perbarui desain antarmuka daftar mata kuliah dan tata letak css
  ```

### 5. Evaluasi Commit Terbesar (`git log --stat`)
* **Commit Terbesar yang Teridentifikasi :**  
  `4079539 docs(deployment): melengkapi panduan produksi analisis kendala dan bukti curl`  
  *(Perubahan: 1 berkas, +463 baris / -76 baris)* serta `d593803 feat(rekap-nilai): tampilkan rekap nilai per MK untuk dosen dan mahasiswa` *(+222 baris)*.
* **Evaluasi Pemecahan :**  
  Commit `4079539` mencakup dua topik besar sekaligus: dokumentasi langkah instalasi server produksi dan analisis 7 kendala deployment. Seharusnya commit tersebut dipecah menjadi dua commit terpisah:
  1. docs(deployment): susun panduan instalasi server produksi dan spesifikasi lingkungan
  2. docs(troubleshooting): dokumentasikan analisis 7 kendala produksi beserta mitigasinya

---

## BAGIAN 2: BUILD — Laporan Peer Review (Pull Request #149)

# PEER REVIEW KELOMPOK LAIN — Pull Request #149

## 1. Informasi Pull Request

| Keterangan | Informasi |
|---|---|
| Repository | `https://github.com/Zweilior/kampuslms-kelompok-B4` |
| Pull Request | `#149` *(Merge commit: `5cdd0a2`)* |
| Author | Laudya Aprilia Khoirum (`xcyltra` / `laudya-38` — NIM 10241038) |
| Reviewer | Fatika Rizki Syahada (NIM 10241030 — Kelompok B3) |
| Branch sumber | `laudya-38` |
| Branch tujuan | `main` |
| Fokus perubahan | Fitur pendaftaran massal mahasiswa (*bulk enrollment*) dan penyesuaian tampilan pemilihan mahasiswa |


**Judul perubahan yang diperiksa :**

`feat: add bulk student enrollment selection`

## 2. Berkas yang Diperiksa

| No. | Berkas | Perubahan yang Diperiksa |
|---:|---|---|
| 1 | `app/Http/Controllers/CourseEnrollmentController.php` | Penggantian validasi input `student_ids` (array), penghapusan pagination di `dosenIndex`, dan logika `syncWithoutDetaching`. |
| 2 | `resources/views/dosen/courses/enrollments.blade.php` | Penyesuaian form view dosen dengan input multi-checkbox dan penghapusan render navigasi link pagination. |
| 3 | `resources/views/admin/courses/show.blade.php` | Penyesuaian antarmuka tabel admin untuk aksi pendaftaran massal mahasiswa. |
| 4 | `tests/Feature/DosenCourseEnrollmentTest.php` | Pembaruan assertions test dari pengiriman payload integer `student_id` ke payload array `student_ids`. |

---

## 3. Hasil Pemeriksaan Kode

Pemeriksaan dilakukan berdasarkan diff kode pada branch `laudya-38` (PR #149) dan kaitannya dengan integritas basis data serta performa server produksi.

### A. Keamanan — Pembatasan Ukuran Array Input dan Risiko Orphaned Submissions saat Hapus

**Lokasi pemeriksaan :** `app/Http/Controllers/CourseEnrollmentController.php:17-26` dan `81-90` (method `adminStore()` dan `dosenStore()`).

**Kode yang diperiksa :**

```php
$validated = $request->validate([
    'student_ids' => ['required', 'array', 'min:1'],
    'student_ids.*' => [
        'required',
        Rule::exists('users', 'id')->where('role', 'mahasiswa'),
    ],
]);
```

**Temuan :**

1. Validasi parameter `student_ids` hanya memeriksa batas minimum `'min:1'`, namun **tidak memiliki batasan maksimum elemen** (misalnya `'max:100'`).
2. Pada method penghapusan (`adminDestroy` dan `dosenDestroy`), kode langsung memanggil `$course->students()->detach($student->id)` tanpa memvalidasi apakah mahasiswa yang bersangkutan sudah memiliki riwayat pengumpulan tugas (*submissions*) atau nilai (*grades*) di mata kuliah tersebut.

**Risiko yang perlu diverifikasi :**

* Tanpa batasan ukuran array (`max:`), klien yang mengirimkan request POST berbahaya dapat menginjeksi puluhan ribu ID pengguna sekaligus dalam satu request payload. Hal ini dapat memicu konsumsi CPU tinggi saat validasi database SQL `WHERE IN (...)` dan menyebabkan *lock contention* pada tabel pivot `course_user`.
* Penghapusan mahasiswa yang sudah memiliki submission aktif akan meninggalkan data piatu (*orphaned submissions*) yang berpotensi memicu error *null pointer* atau anomali saat rekapitulasi nilai kelas dijalankan oleh dosen.

**Saran perbaikan :**

1. Tambahkan batas maksimum item array pada validasi request:
   ```php
   'student_ids'   => ['required', 'array', 'min:1', 'max:100'],
   'student_ids.*' => [
       'required',
       'integer',
       Rule::exists('users', 'id')->where('role', 'mahasiswa'),
   ],
   ```
2. Pada method `dosenDestroy` dan `adminDestroy`, tambahkan validasi sebelum *detach* agar tidak menghapus mahasiswa yang sudah memiliki tugas/nilai terkait:
   ```php
   $hasSubmissions = $course->assignments()
       ->whereHas('submissions', fn ($q) => $q->where('user_id', $student->id))
       ->exists();

   if ($hasSubmissions) {
       return back()->with('error', 'Mahasiswa tidak dapat dikeluarkan karena telah memiliki riwayat pengumpulan tugas pada mata kuliah ini.');
   }
   ```

---

### B. Performa — Penghapusan Pagination Menyebabkan Pemuatan Seluruh User ke Memori

**Lokasi pemeriksaan :** `app/Http/Controllers/CourseEnrollmentController.php:57-67` (method `dosenIndex()`).

**Kode yang diperiksa :**

```diff
         $students = $course->students()
             ->where('users.role', 'mahasiswa')
             ->orderBy('users.name')
-            ->paginate(15, ['users.*'], 'students_page')
-            ->withQueryString();
+            ->get(['users.*']);
         $availableStudents = User::query()
             ->where('role', 'mahasiswa')
             ->whereDoesntHave('courses', fn ($query) => $query->where('courses.id', $course->id))
             ->orderBy('name')
-            ->paginate(15, ['users.*'], 'available_students_page')
-            ->withQueryString();
+            ->get();
```

**Temuan :**

Pada PR #149, pemanggilan `paginate(15)` dihapus dan diubah menjadi `->get()` untuk variabel `$students` maupun `$availableStudents`. Kueri `$availableStudents` mencari seluruh pengguna di basis data yang memiliki peran `mahasiswa` dan belum terdaftar di mata kuliah terkait.

**Risiko :**

Pada sistem kampus dengan data realistis (misalnya 3.000–10.000 mahasiswa aktif):
1. Kueri `User::query()->where('role', 'mahasiswa')->...->get()` akan memuat ribuan model Eloquent sekaligus ke dalam memori RAM PHP container worker, berisiko tinggi memicu *Fatal error: Allowed memory size exhausted*.
2. Browser klien akan merender ribuan elemen baris HTML / checkbox DOM sekaligus, mengakibatkan freeze, waktu render lambat, dan waktu muat halaman (*load time*) drop secara drastis.

**Saran perbaikan :**

Kembalikan sistem paginasi atau batasi pencarian mahasiswa yang belum terdaftar menggunakan parameter pencarian (*search filter*) dan limit:

```php
$availableStudents = User::query()
    ->where('role', 'mahasiswa')
    ->whereDoesntHave('courses', fn ($query) => $query->where('courses.id', $course->id))
    ->when($request->filled('search'), function ($query) use ($request) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('nim_nip', 'like', "%{$search}%");
        });
    })
    ->orderBy('name')
    ->paginate(15, ['users.*'], 'available_page')
    ->withQueryString();
```

---

### C. Struktur dan Keterbacaan — Duplikasi Logika 100% Antara Role Admin dan Dosen

**Lokasi pemeriksaan :** `app/Http/Controllers/CourseEnrollmentController.php:15-37` dan `74-96` (`adminStore` vs `dosenStore`).

**Kode yang diperiksa :**

```php
// adminStore (baris 15-37)
Gate::authorize('manageEnrollment', $course);
$validated = $request->validate([ ... ]);
$enrollments = collect($validated['student_ids'])
    ->mapWithKeys(fn ($studentId) => [$studentId => ['enrolled_at' => now()]])
    ->all();
$course->students()->syncWithoutDetaching($enrollments);

// dosenStore (baris 74-96)
Gate::authorize('manageEnrollment', $course);
$validated = $request->validate([ ... ]);
$enrollments = collect($validated['student_ids'])
    ->mapWithKeys(fn ($studentId) => [$studentId => ['enrolled_at' => now()]])
    ->all();
$course->students()->syncWithoutDetaching($enrollments);
```

**Temuan :**

Method `adminStore()` dan `dosenStore()` memiliki isi kode, aturan validasi, pesan error, serta logika sinkronisasi relasi pivot yang **100% identik**. Hal serupa juga terjadi pada method `adminDestroy()` dan `dosenDestroy()`.

**Dampak :**

* Melanggar prinsip *DRY (Don't Repeat Yourself)*.
* Jika suatu saat ada penyesuaian aturan bisnis enrollment (misalnya penambahan batasan kuota kelas atau logging audit), pengembang harus mengubah dua method terpisah secara manual. Ketinggalan mengedit salah satunya akan menimbulkan inkonsistensi perilaku aplikasi.

**Saran perbaikan :**

Satukan logika pendaftaran ke method tunggal `store()` yang mengandalkan otorisasi Policy:

```php
public function store(Request $request, Course $course)
{
    Gate::authorize('manageEnrollment', $course);

    $validated = $request->validate([
        'student_ids'   => ['required', 'array', 'min:1', 'max:100'],
        'student_ids.*' => ['required', Rule::exists('users', 'id')->where('role', 'mahasiswa')],
    ]);

    $enrollments = collect($validated['student_ids'])
        ->mapWithKeys(fn ($studentId) => [$studentId => ['enrolled_at' => now()]])
        ->all();

    $course->students()->syncWithoutDetaching($enrollments);

    return back()->with('success', 'Mahasiswa berhasil didaftarkan ke mata kuliah.');
}
```

---

## 4. Ringkasan Temuan Review

| No. | Kategori | Berkas & Baris | Temuan Utama | Rekomendasi Solusi |
|---:|---|---|---|---|
| 1 | **Keamanan** | `app/Http/Controllers/CourseEnrollmentController.php:17-26` | Tidak ada batasan `max` pada array `student_ids` dan tidak ada proteksi saat menghapus mahasiswa yang sudah memiliki submission. | Tambahkan aturan `'max:100'` dan periksa relasi submission sebelum detach. |
| 2 | **Performa** | `app/Http/Controllers/CourseEnrollmentController.php:57-67` | Menghapus `paginate(15)` pada `dosenIndex` berisiko memicu lonjakan memori saat jumlah mahasiswa ribuan. | Pertahankan pagination atau tambahkan kueri filter pencarian dengan limit. |
| 3 | **Struktur / Keterbacaan** | `app/Http/Controllers/CourseEnrollmentController.php:74-96` | Duplikasi logika 100% antara `adminStore` dan `dosenStore` (serta `adminDestroy` dan `dosenDestroy`). | Satukan logika pendaftaran ke method tunggal atau helper internal controller. |

---

## 5. Komentar untuk Pull Request GitHub

### Komentar 1 — Keamanan

**File :** `app/Http/Controllers/CourseEnrollmentController.php:17-26`

Pada validasi input `student_ids`, saat ini baru terdapat batasan `'min:1'` tanpa pembatasan jumlah maksimal elemen array (`max:`). Tanpa batas atas, pengguna atau klien API berpotensi mengirim ratusan/ribuan ID sekaligus yang membebani kueri database dan mengunci tabel pivot saat `syncWithoutDetaching`. Selain itu, pada method destroy belum ada verifikasi apakah mahasiswa yang dikeluarkan sudah memiliki nilai atau tugas yang terkumpul. Sebaiknya tambahkan batasan `'max:100'` pada array `student_ids` dan tambahkan validasi sebelum `detach` agar tidak memutus integritas data pengumpulan tugas mahasiswa.

### Komentar 2 — Performa

**File :** `app/Http/Controllers/CourseEnrollmentController.php:57-67`

Penghapusan pagination pada kueri `$availableStudents` dengan beralih ke `->get()` berpotensi memicu lonjakan konsumsi memori (*memory limit exceeded*) jika jumlah mahasiswa kampus mencapai ribuan. Instansiasi ribuan model Eloquent sekaligus ke memori PHP juga akan memperlambat waktu respon server dan merusak performa render di browser dosen. Pertimbangkan untuk tetap menggunakan paginasi (`->paginate(15)`) yang dipadukan dengan kolom pencarian berbasis nama atau NIM.

### Komentar 3 — Struktur dan Keterbacaan

**File :** `app/Http/Controllers/CourseEnrollmentController.php:74-96`

Logika validasi dan sinkronisasi relasi pada `dosenStore` dan `adminStore` saat ini 100% identik, begitu juga antara `dosenDestroy` dan `adminDestroy`. Karena otorisasi keduanya sudah diperiksa secara terpusat melalui `Gate::authorize('manageEnrollment', $course)`, disarankan menyatukan logika ini ke dalam satu method bersama (`store` dan `destroy`) agar mematuhi prinsip DRY dan memudahkan *maintenance* kode ke depan.

---

## 6. Tindak Lanjut Review (Status Saat Ini)

Saat ini laporan review telah diajukan sebagai masukan terhadap Pull Request #149 milik Kelompok B4. Tabel berikut mencatat status pengajuan temuan dan menunggu tanggapan resmi serta commit perbaikan dari author PR (Laudya Aprilia Khoirum / Kelompok B4).

| No. | Aspek Temuan | Tanggapan Author (Kelompok B4) | Status | Tindak Lanjut / Bukti Commit |
|---:|---|---|---|---|
| 1 | **Keamanan**<br>(Batas `max` array & proteksi submission pada enrollment) | *(Menunggu tanggapan author di GitHub PR)* | **Diajukan (Menunggu Respon)** | *(Menunggu commit perbaikan `fix:` dari author)* |
| 2 | **Performa**<br>(Penghapusan paginasi pada daftar mahasiswa) | *(Menunggu tanggapan author di GitHub PR)* | **Diajukan (Menunggu Respon)** | *(Menunggu respon teknis)* |
| 3 | **Struktur & Keterbacaan**<br>(Duplikasi kode 100% antara admin dan dosen) | *(Menunggu tanggapan author di GitHub PR)* | **Diajukan (Menunggu Respon)** | *(Menunggu respon teknis)* |


---

## 7. Kesimpulan

Pull Request #149 pada repositori `kampuslms-kelompok-B4` mempermudah proses administrasi mata kuliah melalui penambahan fitur pemilihan mahasiswa secara massal (*bulk enrollment*).

Meskipun secara fungsional fitur telah berjalan dan didukung feature test yang memadai, audit kode menunjukkan perlunya penguatan pada batas maksimal payload array untuk mencegah *DoS / table locking*, kehati-hatian dalam menghilangkan paginasi pada dataset besar, serta efisiensi struktur method agar tidak terjadi duplikasi logika yang berlebihan. Temuan-temuan ini menjadi masukan konstruktif untuk menjaga keamanan, performa, dan skalabilitas aplikasi KampusLMS.
