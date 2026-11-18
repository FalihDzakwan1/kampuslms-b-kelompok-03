### Nama : Indriani Anwar

#### NIM : 10241036

---

# PEER REVIEW KELOMPOK SENDIRI — Pull Request #62

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
### Nama : Indriani Anwar

#### NIM : 10241036

---

# PEER REVIEW KELOMPOK LAIN — Pull Request #148

## 1. Informasi Pull Request

| Keterangan | Informasi |
|---|---|
| Repository | `Zweilior/kampuslms-kelompok-B4` |
| Pull Request | `#148` |
| Author | `@xcyltra` (Laudya Aprilia Khoirum) |
| Reviewer | Indriani Anwar |
| Branch sumber | `laudya-38` |
| Branch tujuan | `main` |
| Fokus perubahan | Penghapusan pagination pada enrollment dan pendaftaran beberapa mahasiswa sekaligus (*bulk enrollment*) |

**Judul perubahan yang diperiksa :**

`style(enrollment): menghapus pagination dan perubahan enroll`

## 2. Berkas yang Diperiksa

| No. | Berkas | Perubahan |
|---:|---|---|
| 1 | `app/Http/Controllers/CourseEnrollmentController.php` | Menghapus pagination pada daftar mahasiswa terdaftar dan mahasiswa yang tersedia, serta mengubah `dosenStore()` agar menerima array `student_ids[]`. |
| 2 | `resources/views/dosen/courses/enrollments.blade.php` | Mengubah form menjadi multi-select dan menghapus tautan pagination. |
| 3 | `resources/css/dosen/courses.css` | Menambahkan styling pada elemen pilihan multi-select. |
| 4 | `tests/Feature/DosenCourseEnrollmentTest.php` | Menyesuaikan pengujian untuk daftar tanpa pagination dan pendaftaran beberapa mahasiswa sekaligus. |

## 3. Hasil Pemeriksaan Kode

Pemeriksaan dilakukan berdasarkan perubahan yang terlihat pada tab **Files changed** Pull Request #148. Temuan difokuskan pada perubahan penghapusan pagination dan perubahan proses pendaftaran beberapa mahasiswa sekaligus.

### A. Keamanan — Pembatasan jumlah input pada array `student_ids`

**Lokasi pemeriksaan :** `app/Http/Controllers/CourseEnrollmentController.php`, method `dosenStore()`, sekitar baris 78–88.

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

Validasi `student_ids` sudah memastikan bahwa input berbentuk array, berisi minimal satu data, dan setiap ID yang dikirim harus terdaftar sebagai pengguna dengan role `mahasiswa`. Namun, validasi tersebut belum menetapkan batas maksimum jumlah ID dalam satu permintaan. belum ada pembatasan jumlah mahasiswa yang dapat dikirim dalam satu kali request.

**Risiko :**

Request dengan jumlah ID yang sangat besar dapat menyebabkan proses validasi dan sinkronisasi relasi mahasiswa dengan mata kuliah membutuhkan sumber daya server lebih banyak. Semakin banyak ID yang dikirim dalam satu permintaan, semakin besar pula pekerjaan yang harus diproses oleh aplikasi. Kondisi ini berpotensi meningkatkan beban server apabila tidak ada batas jumlah input.

**Saran perbaikan :**

Tetapkan batas maksimum jumlah mahasiswa per satu kali pendaftaran sesuai kebijakan aplikasi. Sebagai contoh, apabila batas operasionalnya 50 mahasiswa per permintaan, validasi dapat ditulis sebagai berikut:

```php
$validated = $request->validate([
    'student_ids' => ['required', 'array', 'min:1', 'max:50'],
    'student_ids.*' => [
        'required',
        Rule::exists('users', 'id')->where('role', 'mahasiswa'),
    ],
]);
```

Aturan `max:50` merupakan contoh pembatasan jumlah mahasiswa dan perlu disesuaikan dengan kebutuhan serta kebijakan pendaftaran pada sistem.

### B. Performa — Pengambilan seluruh mahasiswa terdaftar tanpa pagination

**Lokasi pemeriksaan :** `app/Http/Controllers/CourseEnrollmentController.php`, method `dosenIndex()`, sekitar baris 57–61.

**Kode yang diperiksa :**

```php
$students = $course->students()
    ->where('users.role', 'mahasiswa')
    ->orderBy('users.name')
    ->get(['users.*']);
```

**Perubahan dari kode sebelumnya :**

```php
$students = $course->students()
    ->where('users.role', 'mahasiswa')
    ->orderBy('users.name')
    ->paginate(15, ['users.*'], 'students_page')
    ->withQueryString();
```

**Temuan :**

Pada PR ini, pagination untuk daftar mahasiswa yang sudah terdaftar pada mata kuliah dihapus dan diganti dengan `get()`. Akibatnya, query mengambil seluruh mahasiswa yang terdaftar tanpa membatasi jumlah record per halaman.

**Risiko :**

Jika jumlah mahasiswa dalam suatu mata kuliah bertambah besar, aplikasi harus mengambil dan memuat seluruh data mahasiswa sekaligus. Hal tersebut dapat meningkatkan penggunaan memori dan waktu pemrosesan pada server. Daftar yang lebih panjang juga membutuhkan lebih banyak elemen HTML untuk ditampilkan pada browser.

**Saran perbaikan :**

Pertahankan pagination pada daftar mahasiswa yang sudah terdaftar, misalnya menggunakan `paginate(15)`. Dengan demikian, aplikasi hanya mengambil sejumlah data sesuai batas halaman dan pengguna tetap dapat berpindah halaman untuk melihat seluruh daftar.

### C. Performa — Pengambilan seluruh mahasiswa yang belum terdaftar tanpa pagination

**Lokasi pemeriksaan :** `app/Http/Controllers/CourseEnrollmentController.php`, method `dosenIndex()`, sekitar baris 62–68.

**Kode yang diperiksa :**

```php
$availableStudents = User::query()
    ->where('role', 'mahasiswa')
    ->whereDoesntHave('courses', fn ($query) => $query->where('courses.id', $course->id))
    ->orderBy('name')
    ->get();
```

**Perubahan dari kode sebelumnya :**

```php
$availableStudents = User::query()
    ->where('role', 'mahasiswa')
    ->whereDoesntHave('courses', fn ($query) => $query->where('courses.id', $course->id))
    ->orderBy('name')
    ->paginate(15, ['*'], 'available_students_page')
    ->withQueryString();
```

**Temuan :**

Pagination pada daftar mahasiswa yang belum terdaftar juga dihapus dan diganti dengan `get()`. Query `whereDoesntHave()` tetap digunakan untuk mencari mahasiswa yang belum terhubung dengan mata kuliah, tetapi hasilnya sekarang diambil seluruhnya tanpa batas jumlah data.

**Risiko :**

Ketika jumlah mahasiswa semakin besar, server harus memuat seluruh hasil query ke memori. Selain itu, seluruh mahasiswa yang tersedia ditampilkan sebagai elemen `<option>` pada multi-select. Jika jumlahnya sangat banyak, ukuran halaman dan jumlah elemen DOM meningkat sehingga proses pemuatan serta interaksi pada browser dapat menjadi lebih lambat.

**Saran perbaikan :**

Gunakan pencarian dinamis (*search/autocomplete*) agar daftar mahasiswa diambil sesuai kata kunci dan kebutuhan pengguna. Alternatifnya, gunakan pagination atau pembatasan jumlah hasil pencarian. Jika kebutuhan dosen adalah memilih beberapa mahasiswa sekaligus, komponen pencarian perlu mempertahankan pilihan yang sudah dipilih meskipun pengguna melakukan pencarian baru.

### D. Struktur dan Keterbacaan — Pemanggilan `array_map()` berulang di dalam perulangan Blade

**Lokasi pemeriksaan :** `resources/views/dosen/courses/enrollments.blade.php`, sekitar baris 40–46.

**Kode yang diperiksa :**

```blade
<select id="student_ids" name="student_ids[]" multiple required size="8">
    @foreach ($availableStudents as $student)
        <option
            value="{{ $student->id }}"
            @selected(in_array(
                (string) $student->id,
                array_map('strval', old('student_ids', [])),
                true
            ))
        >
            {{ $student->name }} · {{ $student->nim_nip }}
        </option>
    @endforeach
</select>
```

**Temuan :**

Pemanggilan `array_map('strval', old('student_ids', []))` berada di dalam `@foreach`. Akibatnya, proses konversi nilai lama menjadi string dilakukan berulang kali pada setiap mahasiswa, meskipun data yang dikonversi sama.

**Dampak :**

Pengulangan proses tersebut menambah pekerjaan yang sebenarnya dapat dilakukan satu kali sebelum perulangan. Ekspresi `@selected` juga menjadi lebih panjang sehingga template Blade kurang mudah dibaca dan dipelihara.

**Saran perbaikan :**

Siapkan data ID mahasiswa yang sebelumnya dipilih sebelum menjalankan perulangan. Agar pemeriksaan keanggotaan lebih efisien, data dapat diubah menjadi lookup array:

```blade
@php
    $selectedStudentIds = array_fill_keys(
        array_map('strval', old('student_ids', [])),
        true
    );
@endphp

@foreach ($availableStudents as $student)
    <option
        value="{{ $student->id }}"
        @selected(isset($selectedStudentIds[(string) $student->id]))
    >
        {{ $student->name }} · {{ $student->nim_nip }}
    </option>
@endforeach
```

Dengan cara tersebut, normalisasi ID dilakukan satu kali di luar perulangan dan pemeriksaan pilihan menggunakan `isset()`.

## 4. Ringkasan Temuan Review

| No. | Kategori | Temuan | Rekomendasi |
|---:|---|---|---|
| 1 | Keamanan | Validasi `student_ids` belum memiliki batas maksimum jumlah mahasiswa dalam satu request. | Tambahkan aturan `max` sesuai kebijakan jumlah pendaftaran aplikasi. |
| 2 | Performa | Pagination pada daftar `$students` dihapus dan diganti `get()`. | Pertahankan pagination pada daftar mahasiswa terdaftar. |
| 3 | Performa | Pagination pada daftar `$availableStudents` dihapus dan diganti `get()`. | Gunakan pencarian dinamis, pagination, atau pembatasan hasil. |
| 4 | Struktur/keterbacaan | `array_map()` dipanggil berulang di dalam loop Blade. | Siapkan lookup array sebelum perulangan. |


## 5. Komentar untuk Pull Request GitHub

### Komentar 1 — Keamanan

**File :** `app/Http/Controllers/CourseEnrollmentController.php`

Validasi `student_ids` saat ini sudah memastikan input berbentuk array, memiliki minimal satu data, dan setiap ID merupakan pengguna dengan role mahasiswa. Namun, belum terdapat batas maksimum jumlah ID dalam satu request. Jika request memuat jumlah ID yang sangat besar, proses validasi dan sinkronisasi relasi dapat membutuhkan sumber daya server lebih banyak. Sebaiknya tambahkan batas maksimum jumlah mahasiswa sesuai kebijakan pendaftaran, misalnya menggunakan aturan `max:50` apabila batas tersebut sesuai dengan kebutuhan aplikasi.

### Komentar 2 — Performa: daftar mahasiswa terdaftar

**File :** `app/Http/Controllers/CourseEnrollmentController.php`

Pada query `$students`, pagination `paginate(15)` diubah menjadi `get()`. Perubahan ini menyebabkan seluruh mahasiswa yang sudah terdaftar pada mata kuliah diambil sekaligus. Jika jumlah mahasiswa bertambah, penggunaan memori dan waktu pemrosesan server dapat meningkat. Sebaiknya pagination tetap dipertahankan untuk daftar mahasiswa terdaftar agar data dimuat secara bertahap.

### Komentar 3 — Performa: daftar mahasiswa yang tersedia

**File :** `app/Http/Controllers/CourseEnrollmentController.php`

Query `$availableStudents` juga mengalami perubahan dari `paginate(15)` menjadi `get()`. Karena hasil query digunakan sebagai pilihan pada elemen multi-select, seluruh mahasiswa yang belum terdaftar akan dimuat sekaligus ke dalam halaman. Pada jumlah data yang besar, hal ini dapat memperbesar ukuran halaman dan memperlambat interaksi browser. Sebaiknya gunakan pencarian dinamis (*autocomplete*) atau pembatasan hasil pencarian, dengan tetap mempertahankan pilihan mahasiswa yang sudah dipilih.

### Komentar 4 — Struktur dan Keterbacaan

**File :** `resources/views/dosen/courses/enrollments.blade.php`

Di dalam `@foreach`, `array_map('strval', old('student_ids', []))` dipanggil berulang kali untuk setiap mahasiswa, padahal data yang dikonversi sama. Proses normalisasi tersebut sebaiknya dilakukan satu kali sebelum perulangan. Hasilnya dapat disimpan sebagai lookup array dan diperiksa menggunakan `isset()` pada atribut `@selected`. Dengan begitu, ekspresi Blade lebih ringkas dan tidak melakukan konversi array berulang.

## 6. Kesimpulan

Berdasarkan pemeriksaan Pull Request #148, perubahan yang diajukan berfokus pada penghapusan pagination dan penambahan kemampuan pendaftaran beberapa mahasiswa sekaligus (*bulk enrollment*).

Dari sisi keamanan, validasi array `student_ids` belum menetapkan batas maksimum jumlah mahasiswa dalam satu request. Penambahan aturan `max` sesuai kebijakan pendaftaran dapat membatasi ukuran input sehingga aplikasi tidak menerima jumlah data yang terlalu besar dalam satu kali proses.

Dari sisi performa, penghapusan pagination pada daftar mahasiswa yang sudah terdaftar dan mahasiswa yang belum terdaftar menyebabkan seluruh hasil query dimuat sekaligus. Kondisi ini berpotensi meningkatkan penggunaan memori server dan memperlambat pemuatan halaman ketika jumlah mahasiswa bertambah. Kedua query tersebut perlu ditangani sesuai kebutuhan tampilannya, yaitu dengan mempertahankan pagination atau menggunakan pencarian dinamis.

Dari sisi struktur dan keterbacaan, pemanggilan `array_map()` di dalam perulangan Blade menyebabkan proses konversi data yang sama dilakukan berulang kali. Proses tersebut dapat dipindahkan ke luar perulangan agar kode lebih ringkas dan mudah dipelihara.

Secara keseluruhan, review menghasilkan **empat temuan yang mencakup tiga kategori**, yaitu keamanan, performa, serta struktur dan keterbacaan. Setiap temuan dilengkapi lokasi kode, penjelasan risiko atau dampak, dan saran perbaikan yang dapat diterapkan. Hasil akhir tindak lanjut akan dilengkapi setelah author memberikan tanggapan dan bukti perbaikan.

