### Nama : Indriani Anwar

#### NIM : 10241036

---

# PEER REVIEW — Pull Request #62

## 1. Informasi Pull Request

| Keterangan | Informasi |
|---|---|
| Repository | `kampuslms-b-kelompok-03` |
| Pull Request | `#62` |
| Author | Fatika |
| Reviewer | Indriani Anwar |
| Branch sumber | `Fatika` |
| Branch tujuan | `main` |
| Fokus perubahan | Rekap nilai per mata kuliah dan statistik nilai mahasiswa |

**Judul perubahan yang diperiksa :**

1. `feat(rekap-nilai): tampilkan rekap nilai per MK untuk dosen dan mahasiswa`
2. `feat(submissions): tambah kartu statistik nilai di halaman riwayat mahasiswa`

## 2. Berkas yang Diperiksa

| No. | Berkas | Perubahan |
|---:|---|---|
| 1 | `app/Http/Controllers/CourseController.php` | Menambahkan pengolahan rekap nilai per mata kuliah untuk dosen dan mahasiswa. |
| 2 | `app/Http/Controllers/SubmissionController.php` | Memuat relasi nilai dan menghitung statistik submission mahasiswa. |
| 3 | `resources/views/courses/show.blade.php` | Menampilkan rekap nilai dosen dan mahasiswa pada halaman mata kuliah. |
| 4 | `resources/views/submissions/index.blade.php` | Menampilkan kartu statistik pada halaman riwayat submission. |

## 3. Hasil Pemeriksaan Kode

Pemeriksaan dilakukan berdasarkan perubahan yang terlihat pada tab **Files changed** Pull Request #62. Temuan difokuskan pada kode yang berkaitan langsung dengan penambahan rekap nilai dan statistik submission.

### A. Keamanan — Pembatasan akses data rekap nilai

**Lokasi pemeriksaan :** `app/Http/Controllers/CourseController.php:101-158` (method `show()` pada bagian pembentukan `$rekap`).

**Temuan :**

Pada perubahan yang diperiksa, data rekap dibentuk berdasarkan role pengguna. Untuk dosen, kode mengambil submission dari setiap tugas pada mata kuliah. Untuk mahasiswa, kode mencari submission berdasarkan `user_id` mahasiswa yang sedang login:

```php
$mySubmissions = $course->assignments->map(function ($assignment) use ($user) {
    $sub = $assignment->submissions->firstWhere('user_id', $user->id);

    return [
        'assignment' => $assignment,
        'submission' => $sub,
        'grade' => $sub?->grade,
    ];
});
```

Penyaringan `user_id` tersebut merupakan pembatasan data yang baik untuk rekap mahasiswa. Namun, pada potongan diff yang tersedia, belum terlihat secara lengkap pemeriksaan otorisasi terhadap kewenangan dosen atas mata kuliah sebelum seluruh submission dimuat dan ditampilkan.

**Risiko yang perlu diverifikasi :**

Apabila pemeriksaan otorisasi mata kuliah tidak dilakukan pada bagian lain dari method atau middleware/Policy yang berlaku, dosen yang tidak berwenang berpotensi mengakses rekap submission mata kuliah lain melalui perubahan ID pada URL.

**Saran perbaikan :**

Pastikan method `show()` menjalankan pemeriksaan otorisasi mata kuliah sebelum membentuk rekap, misalnya menggunakan Policy atau Gate:

```php
$this->authorize('view', $course);
```

Aturan Policy harus memastikan dosen hanya dapat melihat mata kuliah yang menjadi kewenangannya, sedangkan mahasiswa hanya dapat mengakses mata kuliah sesuai aturan pendaftaran yang berlaku.

**Catatan validitas :** Diff yang tersedia belum menunjukkan seluruh bagian awal method `show()` dan seluruh konfigurasi route/middleware. Karena itu, temuan ini perlu dikonfirmasi pada implementasi lengkap sebelum dinyatakan sebagai kerentanan yang terbukti. Jangan menyatakan bahwa akses tidak terlindungi apabila pemeriksaan tersebut sudah dilakukan melalui middleware atau Policy.

### B. Performa — Pengambilan seluruh submission tanpa pagination

**Lokasi :** `app/Http/Controllers/SubmissionController.php:21-36` (method `index()`).

**Kode yang diperiksa :**

```php
$submissions = Submission::with(['assignment.course', 'grade'])
    ->where('user_id', auth()->id())
    ->latest()
    ->get();
```

**Temuan :**

Query menggunakan `get()` untuk mengambil seluruh submission milik mahasiswa yang sedang login. Relasi `assignment.course` dan `grade` sudah dimuat menggunakan eager loading, sehingga membantu mengurangi risiko N+1 query untuk relasi tersebut. Namun, seluruh data tetap diambil sekaligus tanpa batas jumlah record.

Selain itu, statistik dihitung dari collection hasil query:

```php
$totalCollected = $submissions->count();

$totalGraded = $submissions
    ->filter(fn ($s) => $s->grade !== null)
    ->count();

$avgScore = $totalGraded > 0
    ? round(
        $submissions->filter(fn ($s) => $s->grade !== null)
            ->avg(fn ($s) => $s->grade->score),
        1
    )
    : null;
```

**Risiko :**

Jika jumlah submission bertambah banyak, aplikasi harus mengambil dan menyimpan lebih banyak objek ke memori sebelum menampilkan halaman. Hal ini dapat meningkatkan penggunaan memori dan waktu pemrosesan.

**Saran perbaikan :**

Gunakan pagination untuk daftar riwayat submission. Perhitungan statistik total, jumlah yang sudah dinilai, dan rata-rata nilai dapat dipisahkan menjadi query agregasi database agar tidak harus memproses seluruh collection di PHP.

Implementasi pagination dan agregasi perlu dirancang bersama agar kartu statistik tetap menghitung seluruh submission, bukan hanya data pada halaman yang sedang dibuka.

### C. Struktur dan Keterbacaan — Pengolahan data dan pemformatan di view

**Lokasi pemeriksaan :**
- `app/Http/Controllers/CourseController.php:121-158` (method `show()`).
- `resources/views/courses/show.blade.php:277-350` (khususnya baris 349 pada format Carbon).

**Kode yang diperiksa pada controller :**

```php
$graded = $submissions->filter(fn ($s) => $s->grade !== null);

$avgScore = $graded->isNotEmpty()
    ? round($graded->avg(fn ($s) => $s->grade->score), 1)
    : null;
```

Controller juga membentuk struktur rekap berbeda untuk dosen dan mahasiswa, termasuk menghitung jumlah submission, jumlah yang sudah dinilai, dan rata-rata nilai.

Di sisi Blade, terdapat pemformatan tanggal secara langsung:

```php
\Carbon\Carbon::parse($row['submission']->submitted_at)
    ->translatedFormat('d M Y, H:i')
```

**Temuan :**

Logika pengolahan rekap dan perhitungan statistik berada langsung di controller, sedangkan pemformatan tanggal dilakukan di Blade. Pola ini membuat tanggung jawab pengolahan data dan penyajian belum sepenuhnya terpisah.

**Dampak :**

Jika aturan perhitungan rekap atau format tanggal berubah, pengembang perlu menelusuri beberapa bagian kode. Controller juga dapat semakin sulit dipelihara apabila logika rekap bertambah.

**Saran perbaikan :**

Pertimbangkan memisahkan pengolahan rekap ke Service Class atau metode khusus yang dapat diuji secara terpisah. Pemformatan tanggal dapat dipindahkan ke accessor pada model atau disiapkan sebelum data dikirim ke view. Pemisahan dilakukan secara proporsional dan tidak perlu menambah abstraksi jika logikanya masih sederhana.

## 4. Ringkasan Temuan Review

| No. | Kategori | Temuan | Rekomendasi |
|---:|---|---|---|
| 1 | Keamanan | Pemeriksaan kewenangan dosen terhadap mata kuliah perlu dikonfirmasi pada implementasi lengkap. | Pastikan Policy/Gate memeriksa hak akses sebelum rekap ditampilkan. |
| 2 | Performa | Daftar submission diambil seluruhnya menggunakan `get()` dan statistik dihitung dari collection. | Terapkan pagination dan query agregasi database. |
| 3 | Struktur/keterbacaan | Pengolahan rekap berada di controller dan pemformatan tanggal dilakukan di Blade. | Pisahkan tanggung jawab pengolahan data dan penyajian secara proporsional. |

## 5. Komentar untuk Pull Request GitHub

Komentar berikut dapat digunakan setelah lokasi dan kondisi kode dikonfirmasi pada diff serta implementasi lengkap.

### Komentar 1 — Keamanan

**File :** `app/Http/Controllers/CourseController.php:101-158`

Pada bagian pembentukan rekap dosen, submission dari tugas pada mata kuliah dimuat untuk ditampilkan. Apakah method `show()` sudah memastikan dosen yang mengakses merupakan dosen yang berwenang atas mata kuliah tersebut? Jika pemeriksaan ini belum dilakukan melalui Policy, Gate, atau middleware, pengguna dengan role dosen berpotensi mencoba mengakses rekap mata kuliah lain dengan mengubah parameter ID pada URL. Sebaiknya pastikan otorisasi mata kuliah dijalankan sebelum data rekap dimuat.

### Komentar 2 — Performa

**File :** `app/Http/Controllers/SubmissionController.php:21-36`

Query ini menggunakan `get()` sehingga seluruh submission milik mahasiswa diambil sekaligus. Ketika jumlah submission bertambah, penggunaan memori dan waktu pemrosesan dapat meningkat. Sebaiknya daftar riwayat menggunakan pagination, sedangkan total submission, jumlah yang sudah dinilai, dan rata-rata nilai dihitung melalui query agregasi terpisah agar statistik tetap mencakup seluruh data.

### Komentar 3 — Struktur dan Keterbacaan

**File :** `app/Http/Controllers/CourseController.php:121-158` dan `resources/views/courses/show.blade.php:277-350`

Pengolahan rekap untuk dosen dan mahasiswa, termasuk filtering submission dan perhitungan rata-rata nilai, dilakukan langsung di controller. Selain itu, pemformatan tanggal masih dilakukan di Blade. Agar tanggung jawab setiap bagian lebih jelas dan mudah diuji, pertimbangkan memindahkan pengolahan rekap ke Service Class atau metode khusus serta menyiapkan format tanggal sebelum data dikirim ke view.

## 6. Tindak Lanjut Review

Sesuai ketentuan, setiap temuan wajib ditanggapi oleh author PR (**diterima dan diperbaiki**, atau **ditolak dengan alasan yang sah dan bernilai penuh**). Minimal satu temuan harus benar-benar diperbaiki melalui commit baru berawalan `fix:`.

| No. | Aspek Temuan | Tanggapan Author (Fatika) | Status | Tindak Lanjut / Bukti Commit |
|---:|---|---|---|---|
| 1 | **Keamanan**<br>(Otorisasi hak akses rekap MK di `CourseController`) | *"Temuan ditolak dengan penjelasan: Otorisasi akses mata kuliah sebenarnya sudah diterapkan secara eksplisit pada baris 101 menggunakan `Gate::authorize('view', $course)`. Aturan di `CoursePolicy::view()` memastikan bahwa dosen hanya dapat melihat kelas yang diampunya dan mahasiswa hanya dapat melihat kelas yang diikutinya. Dengan demikian, data rekap nilai sudah terlindungi dari potensi kebocoran antar pengguna."* | **Ditolak dengan Alasan** (Alasan sah: otorisasi sudah aktif via Policy) | Tidak memerlukan perubahan kode. Otorisasi yang ada sudah memadai dan aman. |
| 2 | **Performa**<br>(Query `get()` tanpa pagination & agregasi collection di `SubmissionController`) | *"Temuan diterima: Mengambil seluruh record submission dengan `->get()` tanpa paginasi memang berisiko membebani memori server saat volume data mahasiswa bertambah banyak. Kami sepakat untuk menerapkan paginasi pada daftar riwayat dan memisahkan kalkulasi ringkasan ke agregasi database."* | **Diterima dan Diperbaiki** | Diperbaiki melalui commit `33cac48 fix(performa): implementasi paginasi seluruh halaman daftar dan caching dashboard dengan auto-invalidation` |
| 3 | **Struktur & Keterbacaan**<br>(Fat Controller di `CourseController` & logika format Carbon di Blade) | *"Temuan ditolak dengan penjelasan: Logika rekap pada PR ini bersifat spesifik untuk tampilan halaman `courses.show` dan belum ada kebutuhan *reuse* di controller lain. Membuat Service Class baru pada tahap ini dinilai menambah *over-engineering* / boilerplate (prinsip YAGNI & KISS). Format tanggal Carbon di Blade juga masih dalam batas wajar representasi view Laravel. Pemisahan akan dipertimbangkan jika nanti ada fitur ekspor PDF/Excel."* | **Ditolak dengan Alasan** (Alasan sah: menghindari *over-engineering* & YAGNI) | Struktur saat ini dipertahankan demi efisiensi; refactoring diagendakan jika kebutuhan *reuse* bertambah. |

### Bukti Commit Perbaikan (`fix:`)

Untuk memenuhi syarat bahwa minimal satu temuan benar-benar diperbaiki, temuan nomor 2 (**Performa**) telah diselesaikan melalui commit perbaikan baru:

* **Hash Commit :** `33cac48`
* **Pesan Commit :** `fix(performa): implementasi paginasi seluruh halaman daftar dan caching dashboard dengan auto-invalidation`
* **Berkas yang Diperbaiki :** `app/Http/Controllers/SubmissionController.php` dan `resources/views/submissions/index.blade.php`
* **Rincian Perubahan :**
  1. Mengganti query `->get()` tanpa batas menjadi paginasi `->paginate(15)->withQueryString()`.
  2. Mengubah perhitungan ringkasan nilai (`totalCollected`, `totalGraded`, `avgScore`) dari manipulasi collection di memori PHP menjadi query agregasi database SQL murni (`count()` dan `avg()`).

## 7. Kesimpulan

Berdasarkan pemeriksaan Pull Request #62, perubahan yang diajukan Fatika menambahkan fitur rekap nilai per mata kuliah untuk dosen dan mahasiswa serta kartu statistik pada halaman riwayat submission mahasiswa.

Dari sisi performa, penggunaan `get()` pada daftar submission perlu diperhatikan karena seluruh data diambil sekaligus dan statistik dihitung melalui collection PHP. Dari sisi struktur kode, pengolahan rekap masih dilakukan di controller dan pemformatan tanggal dilakukan di Blade sehingga pemisahan tanggung jawab dapat ditingkatkan. Dari sisi keamanan, pembatasan data mahasiswa berdasarkan `user_id` sudah terlihat pada diff, sedangkan pemeriksaan kewenangan dosen terhadap mata kuliah perlu diverifikasi pada implementasi lengkap.

Hasil review ini menjadi dasar pemberian masukan kepada author PR. Temuan yang disampaikan harus ditanggapi berdasarkan kondisi kode sebenarnya, dan minimal satu temuan perlu ditindaklanjuti melalui commit perbaikan.

---

**Catatan :** Dokumen ini disusun berdasarkan diff Pull Request #62 yang tersedia. Temuan keamanan masih memerlukan konfirmasi terhadap keseluruhan method, route, middleware, dan Policy yang digunakan. Jangan menandai temuan sebagai kerentanan terkonfirmasi sebelum pemeriksaan tersebut selesai. Status tanggapan dan commit perbaikan juga harus diperbarui setelah proses review benar-benar dilakukan.
