**Nama:** Indriani Anwar  
**NIM:** 10241036

## A. READ — Telusuri Satu Berkas

### 1. Temukan berkas fisiknya di sistem berkas. Path lengkapnya apa?

Berkas materi dan submission sebaiknya disimpan di tempat privat karena tidak semua pengguna boleh membukanya. Pada Laravel, file bisa disimpan menggunakan disk `local`, misalnya:

```php
$path = $file->store("materials/{$course->id}", 'local');
```

Jika disk `local` di proyek ini mengarah ke `storage/app/private`, file akan tersimpan di folder tersebut. Bentuk path-nya kira-kira seperti berikut:

```text
storage/app/private/materials/<NAMA_FILE_ACAK>
```
*(atau untuk berkas submission tugas: `storage/app/private/submissions/<ID_TUGAS>/<NAMA_FILE_ACAK>`)*.

Path itu masih berupa contoh. Untuk mengetahui lokasi sebenarnya, saya perlu mengunggah satu file uji, melihat nilai `file_path` di database, lalu mencocokkannya dengan file di File Explorer.

### 2. Buka baris database yang bersangkutan. Bandingkan file_path dengan lokasi fisiknya.

Pada controller submission, data disimpan menggunakan field berikut:

```php
[
    'file_path'     => $path,
    'original_name' => $file->getClientOriginalName(),
    'file_size'     => $file->getSize(),
    'submitted_at'  => now(),
    'is_late'       => $isLate,
]
```

`file_path` menyimpan lokasi relatif file di disk, misalnya `submissions/12/abc123.pdf`. `original_name` menyimpan nama asli file, sedangkan `file_size` menyimpan ukuran file dalam byte.

Jika root disk `local` adalah `storage/app/private`, path tadi seharusnya cocok dengan `storage/app/private/submissions/12/abc123.pdf`. Saya perlu memastikan root disk tersebut di `config/filesystems.php`, terutama karena kode upload sempat diubah dari `local` menjadi `public` saat demonstrasi. File yang diunggah saat itu bisa tersimpan di lokasi berbeda.

### 3. Coba akses berkas itu langsung lewat browser dengan menebak URL-nya. Berhasil atau tidak? Kenapa?

File privat seharusnya tidak bisa dibuka langsung melalui URL publik seperti:

```text
http://127.0.0.1:8000/storage/submissions/12/abc123.pdf
```

URL `/storage/...` biasanya mengarah ke `public/storage`, yang terhubung ke `storage/app/public`, bukan ke `storage/app/private`.

Untuk mengujinya, saya perlu menggunakan path file yang sebenarnya dan membuka URL tanpa login. Jika file privat tidak bisa dibuka langsung (menghasilkan status HTTP 404 Not Found), berarti file tersebut tidak tersedia melalui jalur publik. Akses seharusnya diberikan melalui controller yang memeriksa izin pengguna. Jika file terbuka, lokasi penyimpanan dan konfigurasi server perlu diperiksa kembali.

### 4. Buka config/filesystems.php. Identifikasi disk local dan public — apa persis bedanya?

Perbedaannya dapat dilihat di `config/filesystems.php`, khususnya pengaturan `root`.

| Disk | Lokasi yang diharapkan | Penggunaan | Cara akses |
|---|---|---|---|
| `local` | `storage/app/private` | Materi kuliah dan submission | Melalui controller yang memeriksa izin |
| `public` | `storage/app/public` | Foto profil, logo, dan file yang boleh dilihat umum | Melalui URL setelah `storage:link` |

Saya tetap perlu memeriksa konfigurasi proyek karena lokasi penyimpanan sebenarnya mengikuti nilai `root`, bukan hanya nama disk.

### 5. Jalankan php artisan storage:link. Apa yang dibuatnya? Kenapa materi Anda tidak boleh ada di sana?

Perintah yang digunakan:

```bash
php artisan storage:link
```

Perintah ini biasanya membuat link dari `public/storage` ke `storage/app/public`. Dengan begitu, file di folder publik bisa dibuka melalui URL `/storage/...`.

Materi dan submission tidak boleh disimpan di `storage/app/public` karena orang lain dapat membuka file lewat URL langsung tanpa melewati login dan Policy Laravel. Login tidak otomatis melindungi file yang langsung disajikan oleh web server.

Jika perintah menyatakan link sudah ada, saya tidak perlu membuatnya berulang kali. Saya cukup memastikan link tersebut mengarah ke `storage/app/public`.

## B. BREAK — Delapan kerusakan (50 menit)

### 1. Pindahkan berkas submission ke storage/app/public, akses URL-nya tanpa login

Untuk melihat risiko kebocoran, kode upload diubah sementara menjadi:

```php
$path = $file->store("submissions/{$assignment->id}", 'public');
```

Saya perlu mengunggah ulang file uji karena perubahan kode tidak memindahkan file lama secara otomatis. Setelah itu, saya melihat nilai `file_path` di database dan menyusun URL berdasarkan path tersebut. Misalnya, jika path-nya `submissions/12/abc123.pdf`, URL-nya:

```text
http://127.0.0.1:8000/storage/submissions/12/abc123.pdf
```

URL dibuka tanpa login. Jika file terbuka, berarti file di disk publik bisa diakses langsung tanpa otorisasi aplikasi. Jika belum terbuka, saya perlu memeriksa path, nama file, dan symbolic link.

Setelah pengujian, penyimpanan harus dikembalikan ke disk privat:

```php
$path = $file->store("submissions/{$assignment->id}", 'local');
```

### 2. Hapus Gate::authorize() dari endpoint download, akses submission orang lain

Endpoint download seharusnya memeriksa izin sebelum mengirim file, misalnya dengan:

```php
Gate::authorize('view', $submission);
```

Untuk menguji risiko ini, dua akun mahasiswa uji bisa digunakan. Akun A mengunggah tugas, lalu akun B mencoba mengakses endpoint download milik A. Jika pemeriksaan otorisasi dihapus dan tidak ada pemeriksaan lain, akun B mungkin bisa mengunduh file A dengan mengganti ID pada request. Masalah ini disebut **IDOR (Insecure Direct Object Reference)**.

Pada controller API (`App\Http\Controllers\Api\SubmissionController`), memang belum disediakan endpoint download. Namun pada controller web (`App\Http\Controllers\SubmissionController`), fungsi `download()` sudah diimplementasikan dan telah diamankan dengan `Gate::authorize('view', $submission)` sehingga terlindungi dari celah IDOR.

Perbaikannya adalah memastikan izin selalu diperiksa di server menggunakan `Gate::authorize()` dan Policy yang sesuai. Menyembunyikan tombol download di halaman saja tidak cukup.

### 3. Simpan berkas dengan storeAs() memakai nama asli, lalu dua akun mahasiswa berbeda mengunggah tugas.pdf dengan isi berbeda ke tugas yang sama

Contoh kode yang berisiko:

```php
$path = $file->storeAs(
    "submissions/{$assignment->id}",
    $file->getClientOriginalName(),
    'local'
);
```

Saya bisa mengujinya dengan dua akun uji. Keduanya mengunggah file bernama `tugas.pdf`, tetapi isinya berbeda. Karena nama dan foldernya sama, kedua upload bisa mengarah ke path fisik yang sama. Upload kedua berpotensi menimpa file pertama.

Untuk menghindarinya, gunakan `store()` agar Laravel membuat nama fisik secara otomatis, lalu simpan nama asli di kolom `original_name`. Kode awal `SubmissionController` sudah menggunakan `store()`.

### 4. Unggah berkas PHP yang diganti namanya jadi .pdf dengan validasi extensions, lalu dengan mimes

Perbandingan bisa dilakukan menggunakan file uji yang aman dan diberi nama `uji.pdf`.

Validasi ekstensi:

```php
'file' => ['required', 'file', 'extensions:pdf'],
```

Validasi tipe file:

```php
'file' => ['required', 'file', 'mimes:pdf'],
```

`extensions` hanya memeriksa ekstensi nama file. Jadi, file yang isinya bukan PDF tetapi diberi nama `.pdf` bisa lolos pemeriksaan ekstensi. Sementara itu, `mimes` memeriksa tipe konten yang dikenali Laravel dan seharusnya menolak file yang tidak cocok dengan PDF.

Di proyek ini, aturan yang sudah terlihat adalah `mimes:pdf,doc,docx`. Jika diperlukan, aturan `extensions` juga bisa ditambahkan. Namun, validasi tipe file bukan pengganti pemindaian malware.

### 5. Ubah max:5120 menjadi max:5, unggah berkas 1 MB

Aturan untuk batas sekitar 5 MiB:

```php
'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
```

Jika nilainya diubah menjadi `max:5`, file uji berukuran 1 MB seharusnya ditolak. Alasannya, `max` untuk file memakai satuan kilobyte. Jadi, `max:5` berarti sekitar 5 KB, sedangkan `max:5120` berarti 5.120 KB atau 5 MiB.

Pada kode `SubmissionController` yang saya kirim sebelumnya, batasnya masih `max:2048`, yaitu sekitar 2 MiB. Jika checkpoint meminta batas 5 MiB, nilainya perlu diubah menjadi `max:5120`.

### 6. Set upload_max_filesize=1M di php.ini, unggah berkas 3 MB dengan validasi max:5120

Di `php.ini` Laragon, pengaturan ini bisa diuji sementara:

```ini
upload_max_filesize = 1M
```

Jika Laravel mengizinkan file sampai 5 MiB (`max:5120`), tetapi PHP hanya mengizinkan satu file sampai 1 MB, file 3 MB bisa ditolak sebelum validasi Laravel dijalankan. Artinya, batas server bisa menghentikan upload lebih awal.

Saya juga perlu memeriksa `post_max_size`, karena pengaturan ini membatasi total ukuran satu permintaan POST, termasuk file dan data lain. Sebelumnya saya menemukan `upload_max_filesize = 2G`, artinya PHP mengizinkan satu file sampai 2 GB. Jadi, nilai tersebut bukan penghalang untuk file 5 MB. Nilai `post_max_size` tetap perlu diperiksa.

Setelah pengujian, konfigurasi PHP harus dikembalikan ke nilai yang sesuai dengan kebutuhan aplikasi.

### 7. Hapus updateOrCreate, ganti create, kumpulkan tugas dua kali

Kode yang digunakan pada controller:

```php
Submission::updateOrCreate(
    [
        'assignment_id' => $assignment->id,
        'user_id'       => $user->id,
    ],
    [
        'file_path' => $path,
        // field lain...
    ]
);
```

`updateOrCreate()` mencari submission berdasarkan kombinasi ID tugas dan ID mahasiswa. Jika datanya sudah ada, data diperbarui. Jika belum, data baru dibuat.

Untuk pengujian, kode bisa diganti sementara dengan `create()`, lalu tugas dikumpulkan dua kali menggunakan akun dan tugas yang sama. `create()` akan mencoba membuat baris baru setiap kali. Jika database memiliki unique constraint pada kombinasi `(assignment_id, user_id)`, percobaan kedua seharusnya gagal karena duplikasi. Jika constraint tidak ada, record ganda bisa terbentuk.

Karena itu, `updateOrCreate()` perlu dikembalikan dan unique constraint di database juga harus dipastikan benar-benar ada.

### 8. Hapus satu submission dari database lewat SQL langsung, cek folder storage

Untuk pengujian, saya menggunakan record uji dan mencatat `file_path` terlebih dahulu. Setelah record dihapus melalui SQL atau phpMyAdmin, saya memeriksa apakah file fisiknya masih ada di folder storage.

File bisa tetap tertinggal karena penghapusan langsung lewat SQL tidak menjalankan event Eloquent `deleting` pada model `Submission`. Contoh event yang menghapus file saat model dihapus melalui Eloquent:

```php
protected static function booted(): void
{
    static::deleting(function (Submission $submission) {
        Storage::disk('local')->delete($submission->file_path);
    });
}
```

Event tersebut tidak otomatis dijalankan jika record dihapus langsung melalui SQL. Hal yang sama perlu diperhatikan pada penghapusan cascade di level database. Akibatnya, file bisa tertinggal meskipun baris databasenya sudah hilang. File seperti ini disebut *orphaned file* atau file yatim.

Untuk mencegahnya, file perlu dihapus secara eksplisit sebelum data induk dihapus, atau dibuat proses untuk membersihkan file yang tidak lagi memiliki record database.
