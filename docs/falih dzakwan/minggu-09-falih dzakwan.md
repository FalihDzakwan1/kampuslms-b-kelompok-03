#### Nama : Falih Dzakwan
#### NIM  : 10241028

---

# MINGGU 9 — Unggah Berkas, Penyimpanan Privat, dan Keamanan File

Catatan individu Minggu 9 ini berisi hasil penelusuran alur penyimpanan berkas (**READ**) dan pengujian skenario kerusakan keamanan berkas (**BREAK**) pada repositori `kampuslms-b-kelompok-03`.

---

## READ — Telusuri Satu Berkas (30 Menit)

### 1. Temukan berkas fisiknya di sistem berkas. Path lengkapnya apa?

Ketika login sebagai akun **Dosen** dan mengunggah satu berkas materi perkuliahan pada `MaterialController@store` (`app/Http/Controllers/MaterialController.php:103`), berkas disimpan menggunakan perintah:

```php
$data['file_path'] = $file->store('materials', 'local');
```

Berkas tidak diletakkan di direktori publik (`public/`), melainkan di direktori privat Laravel:
* **Path relatif di proyek:** `storage/app/private/materials/`
* **Path lengkap di lingkungan Windows lokal:** `C:\Users\ASUS\Documents\Mata Kuliah\Semester 6\Proweb\kampuslms-b-kelompok-03\storage\app\private\materials\<hash_acak>.pdf`

Nama berkas asli dari laptop pengguna tidak dijadikan nama berkas fisik di disk. Laravel secara otomatis menghasilkan nama acak unik (hash 40 karakter) agar dua dosen atau mahasiswa yang mengunggah file bernama sama (misalnya `materi.pdf` atau `tugas.pdf`) tidak saling menimpa (*overwrite*) di harddisk server.

---

### 2. Buka baris database yang bersangkutan. Bandingkan `file_path` dengan lokasi fisiknya.

Pada tabel `materials` (dan `submissions` untuk tugas mahasiswa), kolom database menyimpan informasi berkas sebagai berikut:
* `file_path`: hanya menyimpan path relatif terhadap root disk `local`, misalnya `materials/<hash_acak>.pdf` (atau `submissions/{assignment_id}/<hash_acak>.pdf`).
* `original_name`: menyimpan nama berkas asli dari perangkat pengguna (misalnya `Modul_Pertemuan_9.pdf`) agar tetap tampil ramah saat diunduh atau dipratinjau.
* `file_size` dan `mime_type`: menyimpan metadata ukuran (dalam byte) dan tipe MIME aktual berkas.

**Alasan tidak menyimpan path absolut (`C:\Users\...`):**  
Path absolut berbeda antara sistem operasi pengembangan (Windows) dan server produksi (Linux container di `/var/www/html/storage/app/private`). Dengan menyimpan path relatif, aplikasi tetap portabel dan langsung bekerja saat dipindahkan ke server produksi maupun object storage seperti S3 tanpa perlu mengubah isi tabel database.

---

### 3. Coba akses berkas itu langsung lewat browser dengan menebak URL-nya. Berhasil atau tidak? Kenapa?

Jika saya mencoba mengakses langsung lewat browser ke alamat seperti:
* `http://127.0.0.1:8000/storage/app/private/materials/<hash_acak>.pdf`, atau
* `http://127.0.0.1:8000/storage/materials/<hash_acak>.pdf`

Hasilnya **gagal (HTTP 404 Not Found)**.

Hal ini disebabkan oleh dua hal:
1. *Document Root* web server hanya mengarah ke folder `public/`. Direktori `storage/app/private/` berada di luar jangkauan web server sehingga tidak dapat diakses langsung melalui URL statis.
2. Semua akses berkas privat wajib melewati rute controller terproteksi (`materials.download`, `materials.preview`, `submissions.download`, dan `submissions.preview`) yang terlebih dahulu menjalankan middleware `auth` serta `Gate::authorize('view', ...)` sebelum mengalirkan berkas ke peramban.

---

### 4. Buka `config/filesystems.php`. Identifikasi disk `local` dan `public` — apa persis bedanya?

Berdasarkan pemeriksaan pada berkas `config/filesystems.php:33-48`:

| Aspek | Disk `local` | Disk `public` |
|---|---|---|
| **Root Direktori** | `storage_path('app/private')` (`storage/app/private`) | `storage_path('app/public')` (`storage/app/public`) |
| **URL Publik** | Tidak memiliki `url` publik langsung (`serve => true` hanya untuk signed/internal serving via Laravel) | Memiliki `url => env('APP_URL').'/storage'` dan `visibility => 'public'` |
| **Mekanisme Akses** | Privat; wajib dibaca dan dikirim oleh PHP/Controller setelah lolos otorisasi Policy | Terbuka langsung oleh web server melalui *symbolic link* `public/storage` |
| **Peruntukan di KampusLMS** | **Wajib** untuk `materials` dan `submissions` karena bersifat rahasia akademik | Hanya untuk aset non-sensitif (logo publik, avatar umum, aset terbuka) |

---

### 5. Jalankan `php artisan storage:link`. Apa yang dibuatnya? Kenapa materi Anda tidak boleh ada di sana?

Perintah `php artisan storage:link` membaca konfigurasi `'links'` pada `config/filesystems.php:76-78`:

```php
'links' => [
    public_path('storage') => storage_path('app/public'),
],
```

Perintah tersebut membuat sebuah *symbolic link* (pintasan direktori) dari `public/storage` yang mengarah ke `storage/app/public`.

**Mengapa materi kuliah dan jawaban tugas tidak boleh berada di sana:**  
Setiap berkas yang masuk ke `storage/app/public` otomatis dapat diakses secara langsung oleh siapa saja di internet lewat URL `https://domain/storage/...` tanpa melalui middleware `auth` maupun `Gate::authorize()`. Akibatnya, siapa pun (termasuk pengguna anonim atau mahasiswa dari mata kuliah lain) dapat menebak URL berkas dan mengunduh materi maupun jawaban tugas mahasiswa lain tanpa izin.

---

## BREAK — Delapan Kerusakan (50 Menit)

| # | Yang Dicoba | Yang Diamati | Penjelasan Teknis & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Mengubah target disk upload submission dari `'local'` menjadi `'public'`, lalu mengakses URL `/storage/submissions/...` di jendela Incognito tanpa login. | Berkas jawaban tugas PDF langsung terbuka dan terunduh bebas tanpa diminta login. | **Kebocoran Data Akademik & Plagiarisme:** Menyimpan berkas privat di disk `public` menonaktifkan seluruh kontrol otorisasi Laravel karena web server menyajikan file statis secara langsung ke publik. |
| **2** | Menghapus `Gate::authorize('view', $submission)` dari `SubmissionController@download` (atau `preview`), lalu mengganti ID submission pada URL ke milik mahasiswa lain. | Mahasiswa A dapat mengunduh dan melihat file tugas milik Mahasiswa B hanya dengan menebak angka ID di URL. | **Celah IDOR (*Insecure Direct Object Reference*):** Middleware `auth` hanya mengecek apakah pengguna sudah login, bukan apakah file itu miliknya. Otorisasi tingkat objek wajib selalu diverifikasi di controller lewat Policy. |
| **3** | Menyimpan berkas menggunakan `storeAs()` memakai nama asli (`$file->getClientOriginalName()`), lalu dua mahasiswa mengunggah file bernama sama `tugas.pdf`. | Berkas milik mahasiswa pertama langsung tertimpa (*overwrite*) oleh berkas milik mahasiswa kedua. | **Konflik & Hilangnya Berkas:** Banyak pengguna memakai nama file generik yang identik (`tugas1.pdf`, `jawaban.docx`). Selalu gunakan `store()` agar nama file fisik diacak menggunakan hash unik, sementara nama asli cukup disimpan di kolom `original_name`. |
| **4** | Mengubah nama skrip `shell.php` menjadi `shell.pdf` lalu mengujinya dengan aturan validasi `extensions:pdf` dibandingkan `mimes:pdf`. | Validasi `extensions:pdf` meloloskan file tersebut karena hanya memeriksa akhiran string nama file, sedangkan `mimes:pdf` langsung menolak dengan error validasi. | **Spoofing Tipe Berkas (*Fake Extension*):** Aturan `mimes` membaca *magic bytes* / MIME type isi biner berkas sebenarnya, bukan sekadar teks nama file, sehingga mencegah penyusupan skrip berbahaya berkedok PDF. |
| **5** | Mengubah aturan ukuran file pada controller dari `max:5120` menjadi `max:5`, lalu mencoba mengunggah file PDF berukuran 1 MB. | Upload langsung ditolak oleh validasi Laravel dengan pesan bahwa ukuran file terlalu besar. | **Salah Memahami Satuan `max`:** Pada validasi file Laravel, parameter `max` menggunakan satuan **Kilobyte (KB)**, bukan Megabyte. `max:5` berarti hanya 5 KB; untuk batas 5 MB nilai yang benar adalah `max:5120`, dan untuk materi 20 MB adalah `max:20480` (sesuai `MaterialController.php:69`). |
| **6** | Menurunkan konfigurasi `upload_max_filesize = 1M` pada `php.ini`, sementara validasi Laravel mengizinkan `max:5120` (5 MB), lalu mengunggah file 3 MB. | Request gagal di level server PHP sebelum sempat menyentuh kode validasi controller Laravel (berkas terbaca gagal upload / *error code 1*). | **Batas PHP Engine Mendahului Aplikasi:** Batas unggah pada `php.ini` (`upload_max_filesize` dan `post_max_size`) dievaluasi lebih awal oleh web server sebelum framework berjalan. Karena itu pada produksi kami menyetel `docker/php/custom.ini` ke `25M` / `30M` agar lebih besar dari batas validasi aplikasi (20 MB materi / 5 MB tugas). |
| **7** | Mengganti `Submission::updateOrCreate()` di `SubmissionController@store` menjadi `Submission::create()`, lalu mahasiswa mengumpulkan ulang pada tugas yang sama. | Muncul error fatal `SQLSTATE[23000]: Integrity constraint violation` pada percobaan pengumpulan kedua. | **Constraint Unik Database:** Migrasi tabel `submissions` menetapkan indeks unik gabungan pada `(assignment_id, user_id)` untuk menjamin 1 mahasiswa hanya memiliki 1 baris pengumpulan per tugas. Mekanisme `updateOrCreate()` dan penghapusan berkas fisik lama menjaga agar kumpul ulang berjalan mulus tanpa error duplikat. |
| **8** | Menghapus satu baris data submission langsung dari tabel database (via SQL) tanpa melalui controller, lalu memeriksa folder `storage/app/private/submissions/`. | Baris data di database hilang, tetapi berkas fisik di folder `storage/app/private/` tetap tertinggal di harddisk. | **Orphan File / Kebocoran Kapasitas Disk:** Database tidak otomatis menghapus file di disk saat row dihapus. Oleh karena itu, ketika mahasiswa mengumpulkan ulang di `SubmissionController@store:80-82`, controller wajib mengecek dan menghapus berkas fisik lama lewat `Storage::disk('local')->delete($existing->file_path)` agar penyimpanan server tidak penuh oleh file sampah yatim. |

---

## Kesimpulan

Penanganan berkas pada aplikasi web tidak cukup hanya mengandalkan pengunggahan yang berhasil. Setiap berkas akademik privat wajib disimpan di disk `local` (`storage/app/private`), dinamai ulang secara acak dengan `store()`, divalidasi berdasarkan tipe MIME asli serta batas ukuran yang selaras dengan konfigurasi `php.ini`, dibersihkan berkas lamanya saat pembaruan, serta selalu dijaga otorisasinya di controller baik untuk fitur unduh (`download`) maupun pratinjau langsung (`preview`).
