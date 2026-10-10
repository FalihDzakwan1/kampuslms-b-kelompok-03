### Nama: Fatika Rizki Syahada
#### NIM: 10241030

---

# READ — Membandingkan Jalur Web dan API (30 menit)

1. Jalankan php artisan install:api. Baca perubahan yang terjadi di bootstrap/app.php.

Perintah yang dijalankan:

```bash
php artisan install:api
```

Perintah tersebut menyiapkan route API di `routes/api.php` dan memasang Sanctum untuk autentikasi menggunakan token. Konfigurasi pada `bootstrap/app.php` memuat route API dengan prefix `/api`. Karena route proyek memakai prefix `v1`, endpoint courses memiliki alamat `GET /api/v1/courses`.

2. Buat satu endpoint GET /api/v1/courses sederhana.

Endpoint didaftarkan pada `routes/api.php` dengan menghubungkan method GET ke method `index` pada `CourseController` API. Route berada di dalam grup prefix `v1`, sehingga bersama prefix bawaan `/api` membentuk URI `/api/v1/courses`.

Contoh route:

```php
Route::get('/courses', [CourseController::class, 'index']);
```

Pada konfigurasi proyek yang dibagikan, route tersebut berada di dalam grup `auth:sanctum`, sehingga memerlukan token yang valid.

3. Bandingkan dengan CourseController versi web yang sudah ada. Tulis di catatan: apa yang sama dan apa yang berbeda di antara keduanya?

| Aspek | Controller Web | Controller API |
|---|---|---|
| Lokasi umum | `app/Http/Controllers/CourseController.php` | `app/Http/Controllers/Api/CourseController.php` |
| File route | `routes/web.php` | `routes/api.php` |
| URI | Contohnya `/courses` | `/api/v1/courses` |
| Hasil yang diberikan | Halaman HTML melalui Blade | Data dalam bentuk JSON |
| Autentikasi umum | Session dan cookie | Token, misalnya Sanctum |
| Perlindungan CSRF | Umumnya berlaku pada form web | Umumnya tidak digunakan pada API token stateless |
| Peran controller | Menyiapkan data untuk halaman yang dirender server | Menyediakan data agar dapat dipakai berbagai jenis client |
| Client | Browser yang membuka halaman aplikasi | Web frontend, aplikasi mobile, atau layanan lain |

### Kesamaan

| Aspek | Penjelasan |
|---|---|
| Sumber data | Keduanya dapat menggunakan model `Course` untuk membaca atau mengubah data mata kuliah. |
| Tujuan fitur | Keduanya dapat menyediakan operasi daftar, detail, tambah, ubah, dan hapus courses. |
| Aturan bisnis | Aturan aplikasi dan pemeriksaan akses yang sama dapat diterapkan pada kedua jalur. |
| Relasi | Keduanya bisa mengambil relasi, misalnya dosen pengampu, dengan eager loading. |

### Kesimpulan

Controller web dan API dapat bekerja pada data serta aturan bisnis yang sama, tetapi menyajikan hasil dengan cara berbeda. Jalur web biasanya merender halaman Blade untuk browser, sedangkan jalur API mengirim JSON kepada client. Pemisahan ini memungkinkan satu backend dipakai oleh lebih dari satu jenis frontend.

4. Panggil endpoint API tanpa header Accept: application/json. Lalu dengan header itu. Catat bedanya.

| Percobaan | Request | Perbedaan yang diamati atau diharapkan |
|---|---|---|
| Tanpa header JSON | `GET /api/v1/courses` tanpa `Accept` | Laravel tidak menerima petunjuk bahwa client mengharapkan JSON. Jika request gagal, respons dapat berupa halaman HTML atau redirect, bergantung pada jenis error dan konfigurasi aplikasi. |
| Dengan header JSON | `GET /api/v1/courses` dengan `Accept: application/json` | Client menyatakan ingin menerima JSON. Untuk error API seperti autentikasi atau validasi, Laravel dapat mengembalikan respons JSON dengan status yang sesuai, misalnya 401 atau 422. |

Contoh pemanggilan:

```bash
curl -i http://127.0.0.1:8000/api/v1/courses
curl -i -H 'Accept: application/json' http://127.0.0.1:8000/api/v1/courses
```

Header `Accept` menentukan format respons yang diharapkan client; header tersebut tidak menggantikan token Sanctum dan tidak memperbaiki kesalahan route atau controller.

5. Jalankan php artisan route:list --path=api. Cocokkan dengan kontrak di spesifikasi.

Perintah yang digunakan:

```bash
php artisan route:list --path=api
```

Berdasarkan isi `routes/api.php` yang dibagikan, route yang perlu terlihat antara lain:

| Method | URI | Fungsi | Middleware/akses |
|---|---|---|---|
| POST | `/api/v1/auth/login` | Login dan penerbitan token | Publik, dengan `throttle:login` |
| POST | `/api/v1/auth/logout` | Logout | `auth:sanctum` |
| GET | `/api/v1/me` | Informasi pengguna saat ini | `auth:sanctum` |
| GET | `/api/v1/courses` | Daftar courses | `auth:sanctum` |
| GET | `/api/v1/courses/{course}` | Detail course | `auth:sanctum` |
| GET | `/api/v1/courses/{course}/materials` | Materi course | `auth:sanctum` |
| GET | `/api/v1/courses/{course}/assignments` | Assignment course | `auth:sanctum` |
| POST | `/api/v1/assignments` | Membuat assignment | `auth:sanctum` |
| PUT/PATCH | `/api/v1/assignments/{assignment}` | Mengubah assignment | `auth:sanctum` |
| DELETE | `/api/v1/assignments/{assignment}` | Menghapus assignment | `auth:sanctum` |
| GET | `/api/v1/assignments/{assignment}/submissions` | Daftar submission | `auth:sanctum` |
| POST | `/api/v1/assignments/{assignment}/submissions` | Mengirim submission | `auth:sanctum` |
| PUT | `/api/v1/submissions/{submission}/grade` | Memberi atau memperbarui nilai | `auth:sanctum` |



# BREAK — Tujuh Kerusakan (45 menit)

| No. | Percobaan | Hasil pengamatan | Kesimpulan |
|---:|---|---|---|
| 1 | Mengirim `User::all()` sebagai JSON pada endpoint uji; lalu menghapus sementara `$hidden` dari model User | Model mentah dapat menampilkan field yang tidak disembunyikan, seperti email dan kolom pengguna lain. Saat `$hidden` dihilangkan, hash password yang sebelumnya disembunyikan juga dapat ikut tampil. Ini adalah hasil yang diharapkan dari percobaan; belum ada tangkapan hasil aktual yang dicatat. | Model mentah berisiko mengekspos data. Gunakan API Resource untuk memilih secara eksplisit field yang boleh dikirim, lalu pulihkan `$hidden` dan hapus endpoint uji. |
| 2 | Melepas `auth:sanctum` dari grup route lalu membuka `GET /api/v1/courses` tanpa token | **Hasil aktual: 500 Internal Server Error.** Request mencapai `CourseController@index`, lalu gagal di sekitar baris 25 dengan pesan `Attempt to read property "role" on null`. `$request->user()` bernilai `null`, tetapi controller mencoba membaca `$user->role`. Daftar courses tidak berhasil ditampilkan. | Middleware autentikasi memang sudah dilepas, tetapi percobaan ini belum menunjukkan data berhasil terbuka. Controller memiliki ketergantungan pada pengguna yang login. Setelah percobaan, pulihkan `auth:sanctum`. |
| 3 | Memanggil endpoint yang dilindungi dengan token yang dihapus atau tidak valid | **Hasil yang diharapkan:** Laravel menolak request dan mengembalikan `401 Unauthorized`, karena server tidak dapat mengautentikasi peminta. | Token hilang atau tidak valid tidak boleh memberi akses ke route terlindungi. |
| 4 | Login sebagai mahasiswa lalu mengirim `POST /api/v1/assignments` | **Hasil yang diharapkan:** respons `403 Forbidden` jika token valid tetapi mahasiswa tidak memiliki izin membuat assignment. | `401` berarti belum terautentikasi; `403` berarti sudah terautentikasi tetapi tidak memiliki izin. |
| 5 | Menghilangkan eager loading pada daftar courses dan memeriksa query | **Hasil yang diharapkan:** query bertambah karena relasi, misalnya dosen, dimuat satu per satu untuk setiap course. | Pola tersebut adalah N+1. Gunakan eager loading seperti `Course::with('lecturer')` dan sertakan relasi melalui Resource hanya jika sudah dimuat. |
| 6 | Menghilangkan throttle pada login lalu mengirim percobaan berulang | **Hasil yang diharapkan:** login dapat menerima percobaan berulang tanpa batas rate yang ditentukan. | Throttle membatasi laju percobaan dan membantu mengurangi risiko brute force. Pada route proyek, login menggunakan `throttle:login`; konfigurasi limit-nya perlu diperiksa di aplikasi. |
| 7 | Menggunakan pesan berbeda untuk email tidak terdaftar dan password yang salah | **Hasil yang diharapkan:** perbedaan pesan dapat dipakai untuk menebak alamat email mana yang memiliki akun. | Kondisi ini disebut user enumeration. Gunakan pesan generik yang sama, misalnya “Email atau kata sandi salah.” |

