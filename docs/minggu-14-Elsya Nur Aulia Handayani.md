# Catatan Individu Minggu - 14 - Elsya Nur Aulia Handayani (10241026)

## 14.3 Read → Break → Fix → Build

---

### READ — Baca kegagalan (30 menit)

Setelah mempelajari pengujian otomatis (*automated testing*) dan cara kerja pelaporan error di Laravel, saya melakukan serangkaian pengujian langsung di proyek KampusLMS:

#### 1. Jalankan `php artisan test`. Catat berapa test yang ada dan berapa yang lolos.

Saya menjalankan perintah pengujian otomatis seluruh aplikasi melalui terminal menggunakan perintah:
```bash
php artisan test
```

* **Total Test yang Ada :** Terdapat **108 pengujian** (107 Feature Test di dalam folder `tests/Feature/` dan 1 Unit Test bawaan).
* **Jumlah Test yang Lolos :** **108 lolos (100% Passed)** dengan status hijau tanpa ada error.
* **Cakupan Pengujian :** Di dalam berkas utama `tests/Feature/CriticalFlowsTest.php`, terdapat 22 skenario alur kritis yang menguji fungsi login, hak akses admin/dosen/mahasiswa pada CRUD mata kuliah, isolasi data enrollment mahasiswa, upload materi ke disk privat, pengumpulan tugas dan batas waktu, izin unduh berkas, penilaian tugas dosen, dan pengiriman notifikasi. Seluruh pengujian berjalan cepat dalam waktu sekitar 4,6 detik karena menggunakan database SQLite in-memory (`:memory:`).

---

#### 2. Buat satu test sengaja gagal. Baca keluarannya: baris mana yang menyebut berkas Anda?

Saya mencoba membuat satu pengujian sengaja gagal untuk melihat bagaimana format pesan error yang dikeluarkan oleh PHPUnit. Pada file `tests/Feature/CriticalFlowsTest.php` di bagian `test_01_login_berhasil_dengan_kredensial_valid`, baris pengecekan redirect sengaja saya ganti ke rute yang salah:

```php
// Kode asli:
$response->assertRedirect(route('courses.index'));

// Sengaja saya ubah menjadi salah:
$response->assertRedirect('/halaman-ngawur');
```

Ketika dijalankan, terminal langsung memunculkan kotak merah dengan pesan:
```text
FAILED  Tests\Feature\CriticalFlowsTest > 01 login berhasil dengan kredensial valid
Response status code [302] is a redirect, but was redirected to [http://localhost/courses] instead of [http://localhost/halaman-ngawur].
Failed asserting that two URIs are equal.

at tests/Feature/CriticalFlowsTest.php:75
   71▕         $response = $this->post('/login', [
   72▕             'email' => $user->email,
   73▕             'password' => 'password123',
   74▕         ]);
➜  75▕         $response->assertRedirect('/halaman-ngawur');
   76▕         $this->assertAuthenticatedAs($user);
```

* **Baris yang Menyebut Berkas Saya :**  
  Tepat pada baris: **`at tests/Feature/CriticalFlowsTest.php:75`**.
* **Cara Membacanya :**  
  Bagian atas menjelaskan ketidakcocokan hasil (aplikasi aslinya mengarahkan ke `/courses`, tapi test menuntut rute `/halaman-ngawur`). Baris ke-75 adalah nomor baris di dalam file kodingan test saya sendiri yang menjadi sumber penyebab kegagalan tersebut, sehingga saya bisa langsung melompat ke baris tersebut untuk memperbaikinya.

---

#### 3. Buka `storage/logs/laravel.log`, cari satu exception lama. Telusuri stack trace-nya sampai menemukan baris pertama di `app/`.

Saya membuka berkas riwayat log di `storage/logs/laravel.log` untuk melacak error database yang pernah terjadi di proyek ini:

```text
[2026-09-03 04:41:32] local.ERROR: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'kampus_db.courses' doesn't exist 
(Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: kampus_db, SQL: select * from `courses`)
```

Pada tumpukan *stack trace* di bawahnya:
```text
#0 C:/laragon/www/Proweb/kampuslms-b-kelompok-03/vendor/laravel/framework/src/Illuminate/Database/Connection.php(794): ...
#1 C:/laragon/www/Proweb/kampuslms-b-kelompok-03/vendor/laravel/framework/src/Illuminate/Database/Connection.php(411): ...
...
#8 C:/laragon/www/Proweb/kampuslms-b-kelompok-03/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php(770): ...
#9 C:/laragon/www/Proweb/kampuslms-b-kelompok-03/app/Http/Controllers/CourseController.php(11): Illuminate/Database/Eloquent/Model::all()
#10 C:/laragon/www/Proweb/kampuslms-b-kelompok-03/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(46): ...
```

* **Baris Pertama yang Menyebut Direktori `app/` :**  
  **`#9 app/Http/Controllers/CourseController.php:11`**
* **Penjelasan Alur Masalah :**  
  Tumpukan trace dari urutan `#0` sampai `#8` merupakan alur internal framework Laravel di dalam folder `vendor/`. Titik awal kesalahan dari kode yang kami tulis sendiri ada di urutan `#9`, yaitu di file `CourseController.php` baris ke-11 saat memanggil fungsi `Course::all()`. Error ini terjadi karena saat itu database MySQL lokal belum dijalankan proses migrasinya, sehingga tabel `courses` belum ada saat dipanggil oleh controller.

---

#### 4. Pasang `DB::listen()` di `AppServiceProvider`, buka halaman rekap nilai, baca log query-nya.

Saya menambahkan fungsi penyadap query database pada method `boot()` di file `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

public function boot(): void
{
    DB::listen(function ($query) {
        Log::info("SQL Query: {$query->sql} | Waktu: {$query->time}ms");
    });
}
```

Setelah itu, saya membuka halaman rekapitulasi nilai dan detail mata kuliah (`GET /courses/{id}`). Berikut daftar query SQL yang tercatat di dalam log:
1. `select * from "courses" where "id" = ? limit 1` *(Mengambil data mata kuliah)*
2. `select * from "users" where "id" = ?` *(Mengambil relasi dosen pengampu)*
3. `select * from "materials" where "course_id" = ?` *(Mengambil berkas materi perkuliahan)*
4. `select * from "users" where "id" in (?, ?)` *(Mengambil data dosen pengunggah materi)*
5. `select * from "assignments" where "course_id" = ?` *(Mengambil daftar tugas kuliah)*
6. `select * from "submissions" where "assignment_id" in (?, ?)` *(Mengambil berkas pengumpulan tugas mahasiswa)*
7. `select * from "grades" where "submission_id" in (?, ?, ?)` *(Mengambil data nilai tugas)*

* **Hasil Pengamatan :**  
  Total query yang berjalan hanya **7 query**, jauh di bawah batas maksimal toleransi performa (15 query). Halaman rekap nilai terbebas dari masalah N+1 query karena pemanggilan relasi sudah digabungkan sejak awal menggunakan eager loading di controller (`with(['lecturer', 'materials.uploader', 'assignments.submissions.grade'])`).

---

### BREAK — Tujuh percobaan (45 menit)

Dokumentasi hasil eksperimen ketika saya sengaja mencoba merusak setelan pengujian dan kode aplikasi untuk melihat risikonya:

| # | Yang dicoba | Yang harus Anda amati | Penjelasan Teknis & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Tulis test yang hanya `assertStatus(200)`, lalu hapus seluruh isi controller-nya | Test tetap hijau | **Hijau Palsu (*False Positive*).** `assertStatus(200)` cuma mengecek apakah respon server tidak error (kode 200 OK). Pengecekan ini sama sekali tidak memeriksa apakah data tersimpan di database atau muncul di layar. Jika isi controller dihapus dan hanya me-return view kosong, test tetap berwarna hijau padahal aplikasi aslinya sudah rusak dan tidak memproses data apa pun. |
| **2** | Hapus `Gate::authorize()` dari satu controller, jalankan test | Apakah ada test yang merah? Kalau tidak, itu celah | **Celah Otorisasi Terbuka (*Broken Access Control*).** Pada proyek kami hasilnya **langsung MERAH**, karena kami memiliki berkas `CriticalFlowsTest.php` dan `AuthorizationTest.php` yang menguji skenario terlarang (misalnya mahasiswa dilarang mengedit tugas dosen dan wajib menghasilkan error 403 Forbidden). Jika sebuah proyek tidak memiliki test penolakan untuk role terlarang, test akan tetap hijau saat otorisasi dihapus. Ini berbahaya karena mahasiswa bisa bebas mengubah nilai atau materi tanpa dicegah sistem. |
| **3** | Hapus `RefreshDatabase`, jalankan seluruh test dua kali | Test saling mengotori | **Data Uji Bertumpuk (*Data Pollution*).** `RefreshDatabase` bertugas mereset database ke kondisi kosong dan bersih sebelum tiap method test berjalan. Jika baris ini dihapus, data yang dibuat pada pengujian pertama (seperti email pengguna) masih tertinggal di database. Saat pengujian kedua berjalan, test langsung error karena terjadi tabrakan data ganda (*unique constraint violation*). |
| **4** | Tulis test yang bergantung pada data seeder, jalankan di CI | Gagal karena database CI berbeda | **Gagal di Lingkungan Bersih CI.** Server CI (GitHub Actions) membuat database pengujian dari nol tanpa data lama. Jika kodingan test bergantung pada data seeder bawaan (misalnya menganggap pasti ada akun dengan `id = 1`), test akan langsung gagal di server CI. Pengujian yang benar harus mandiri (*self-contained*) dengan membuat datanya sendiri memakai model factory (`User::factory()->create()`). |
| **5** | Hapus `Storage::fake()`, jalankan test unggah | Berkas sungguhan menumpuk di storage/ | **Penyimpanan Harddisk Penuh Berkas Sampah.** Perintah `Storage::fake('local')` membuat media penyimpanan virtual di memori RAM yang otomatis dibuang setelah test selesai. Jika baris ini dihapus, berkas tugas dummy yang diunggah saat pengujian otomatis akan benar-benar tersimpan ke folder harddisk `storage/app/`. Berkas-berkas ini akan menumpuk dan membebani ruang penyimpanan server. |
| **6** | Rusak satu migrasi `down()`, jalankan test | `RefreshDatabase` gagal | **Proses Reset Database Gagal.** `RefreshDatabase` bekerja dengan cara memutar balik (*rollback*) tabel menggunakan perintah di method `down()`, lalu melakukan migrasi ulang. Jika method `down()` rusak, Laravel tidak bisa menghapus tabel lama, sehingga proses migrasi ulang gagal dengan pesan error bahwa tabel sudah ada (*table already exists*). |
| **7** | Tinggalkan `dd()` di controller, jalankan test | Seluruh test setelahnya berantakan | **Eksekusi Aplikasi Terhenti Tiba-tiba.** Perintah `dd()` (*dump and die*) menghentikan eksekusi kode PHP secara mendadak dan mengirim teks debug mentah ke browser. Akibatnya, seluruh test yang melewati controller tersebut langsung gagal karena tidak pernah menerima respon sukses atau redirect yang diharapkan. |

---

#### Catatan Inti Percobaan 1 dan 2:
Nomor 1 dan 2 adalah inti dari pengujian perangkat lunak:
* **Pada Nomor 1 :** Menunjukkan bahwa sekadar mengecek status 200 tidak menjamin fungsionalitas berjalan benar. Setiap pengujian wajib dilengkapi pengecekan kondisi data nyata melalui `assertDatabaseHas()` dan `assertSee()`.
* **Pada Nomor 2 :** Pengujian hak akses jalur negatif (*negative test*) adalah benteng utama keamanan aplikasi. Jika menghapus `Gate::authorize()` tidak membuat satu pun test menjadi merah, itu bukti bahwa sistem keamanan aplikasi sama sekali tidak terjaga oleh pengujian otomatis.

---

